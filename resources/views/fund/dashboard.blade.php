@extends('layouts.fund', ['title' => 'Fund Collection Desk'])

@section('content')
<script>
function fundDesk() {
    return {
        amount: {{ old('amount', 500) }},
        paymentMethod: '{{ old('payment_method', 'cash') }}',
        convertAmountToWords(num) {
            num = Number(num);
            if (!num || num <= 0) return 'Zero Rupees Only';
            
            const words = {
                0: '', 1: 'One', 2: 'Two', 3: 'Three', 4: 'Four', 5: 'Five',
                6: 'Six', 7: 'Seven', 8: 'Eight', 9: 'Nine', 10: 'Ten',
                11: 'Eleven', 12: 'Twelve', 13: 'Thirteen', 14: 'Fourteen', 15: 'Fifteen',
                16: 'Sixteen', 17: 'Seventeen', 18: 'Eighteen', 19: 'Nineteen', 20: 'Twenty',
                30: 'Thirty', 40: 'Forty', 50: 'Fifty', 60: 'Sixty', 70: 'Seventy',
                80: 'Eighty', 90: 'Ninety'
            };
            
            function subHundred(val) {
                if (val <= 20) return words[val] || '';
                const tens = Math.floor(val / 10) * 10;
                const units = val % 10;
                return ((words[tens] || '') + ' ' + (words[units] || '')).trim();
            }

            let no = Math.floor(num);
            let parts = [];

            if (no >= 10000000) {
                let crores = Math.floor(no / 10000000);
                parts.push(subHundred(crores) + ' Crore');
                no %= 10000000;
            }
            if (no >= 100000) {
                let lakhs = Math.floor(no / 100000);
                parts.push(subHundred(lakhs) + ' Lakh');
                no %= 100000;
            }
            if (no >= 1000) {
                let thousands = Math.floor(no / 1000);
                parts.push(subHundred(thousands) + ' Thousand');
                no %= 1000;
            }
            if (no >= 100) {
                let hundreds = Math.floor(no / 100);
                parts.push((words[hundreds] || '') + ' Hundred');
                no %= 100;
            }
            if (no > 0) {
                parts.push(subHundred(no));
            }

            return parts.filter(Boolean).join(' ') + ' Rupees Only';
        }
    };
}
</script>

<div class="space-y-8" x-data="fundDesk()">

    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-emerald-600 text-white">
                    Official Desk
                </span>
                <span class="text-xs text-slate-500 font-mono">QUAF Season 09</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                <span>Fund Collection Desk</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-anek">
                ഫെസ്റ്റിവൽ ഫണ്ട് സംഭാവനകൾ സ്വീകരിക്കുകയും ഡിജിറ്റൽ രസീത് നൽകുകയും ചെയ്യുക (Mahabba & Festival Fund Receipts).
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="px-4 py-2.5 rounded-2xl bg-slate-900 text-white border border-slate-800 shadow-xs text-left">
                <div class="text-[10px] font-mono font-bold uppercase text-slate-400">Next Receipt Number</div>
                <div class="text-sm font-black font-mono text-emerald-400 tracking-wider">{{ $nextReceiptNumber }}</div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Receipts -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400">Total Receipts</div>
                <div class="text-2xl font-black text-slate-900 mt-0.5">{{ number_format($totalCount) }}</div>
            </div>
        </div>

        <!-- Total Amount -->
        <div class="bg-white p-5 rounded-2xl border border-emerald-200/80 bg-emerald-50/20 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                <span class="text-lg font-black font-mono">₹</span>
            </div>
            <div>
                <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-emerald-800">Total Collected</div>
                <div class="text-2xl font-black text-emerald-700 mt-0.5">₹{{ number_format($totalAmount, 2) }}</div>
            </div>
        </div>

        <!-- Today's Collection -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400">Today's Collection</div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 mt-0.5">₹{{ number_format($todayAmount, 2) }}</div>
                <div class="text-[10px] text-slate-400 font-mono">{{ $todayCount }} receipts today</div>
            </div>
        </div>

        <!-- Mode Breakdown -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400">Payment Breakdown</div>
                <div class="text-xs font-bold text-slate-800 mt-1">Cash: <span class="font-mono text-emerald-600">₹{{ number_format($cashAmount) }}</span></div>
                <div class="text-xs font-bold text-slate-800 mt-0.5">UPI/Bank: <span class="font-mono text-indigo-600">₹{{ number_format($upiAmount) }}</span></div>
            </div>
        </div>
    </div>

    <!-- Main Receipt Generation Form -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden" id="new-receipt-card">
        <div class="px-6 py-5 border-b border-slate-200/80 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">Issue New Receipt (പുതിയ രസീത് നൽകുക)</h2>
                    <p class="text-xs text-slate-500 font-anek">ദായകന്റെ വിവരങ്ങളും തുകയും രേഖപ്പെടുത്തി ഉടൻ രസീത് പ്രിന്റ് ചെയ്യുക</p>
                </div>
            </div>

            <div class="text-xs font-mono font-bold px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">
                Receipt Auto-Seq: {{ $nextReceiptNumber }}
            </div>
        </div>

        <form method="POST" action="{{ route('fund.receipts.store') }}" class="p-6 sm:p-8 space-y-6">
            @csrf

            <!-- Form Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Donor Name -->
                <div class="space-y-1.5 md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Donor Name / ദാതാവിന്റെ പേര് <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="donor_name" value="{{ old('donor_name') }}" required
                           placeholder="ദാതാവിന്റെ പേര് (ഇംഗ്ലീഷിലോ മലയാളത്തിലോ നൽകാം)"
                           style="font-family: 'Sora', 'Anek Malayalam', sans-serif;"
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-sm font-semibold text-slate-900 placeholder-slate-400">
                    <p class="text-[11px] text-slate-500 font-anek mt-1">
                        ഇംഗ്ലീഷിൽ ടൈപ്പ് ചെയ്താൽ Sora ഫോണ്ടിലും, മലയാളത്തിൽ ടൈപ്പ് ചെയ്താൽ Anek Malayalam ഫോണ്ടിലും രസീതിൽ പ്രിന്റ് ചെയ്യപ്പെടും.
                    </p>
                    @error('donor_name')
                        <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Donor Phone -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Mobile Number / ഫോൺ നമ്പർ
                    </label>
                    <input type="tel" name="donor_phone" value="{{ old('donor_phone') }}"
                           placeholder="+91 98470 00000"
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-sm font-mono text-slate-900 placeholder-slate-400">
                    @error('donor_phone')
                        <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Donor Place / City -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Place / സ്ഥലം
                    </label>
                    <input type="text" name="donor_place" value="{{ old('donor_place') }}"
                           placeholder="e.g. Kozhikode, Malappuram"
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-sm text-slate-900 placeholder-slate-400">
                </div>

                <!-- Amount -->
                <div class="space-y-1.5 md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Contribution Amount (INR) / സംഭാവന തുക <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base font-black font-mono text-slate-400">
                            ₹
                        </span>
                        <input type="number" step="1" min="1" max="10000000" name="amount" x-model.number="amount" required
                               placeholder="5000"
                               class="w-full pl-9 pr-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-lg font-black font-mono text-slate-900 placeholder-slate-400">
                    </div>
                    @error('amount')
                        <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                    @enderror

                    <!-- Quick Amount Chips -->
                    <div class="flex flex-wrap items-center gap-2 pt-2">
                        <span class="text-[11px] font-bold text-slate-400 uppercase font-mono mr-1">Quick Select:</span>
                        <template x-for="val in [500, 1000, 2000, 5000, 10000, 25000, 50000]" :key="val">
                            <button type="button" @click="amount = val" 
                                    :class="amount === val ? 'bg-emerald-600 text-white border-emerald-600 font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200 font-semibold'"
                                    class="px-2.5 py-1 text-xs rounded-lg border font-mono transition-colors">
                                ₹<span x-text="val.toLocaleString('en-IN')"></span>
                            </button>
                        </template>
                    </div>

                    <!-- Live Words Preview -->
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono text-slate-600 flex items-center gap-2 mt-2" x-show="amount > 0">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">In Words:</span>
                        <span class="font-bold text-emerald-800" x-text="convertAmountToWords(amount)"></span>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Payment Mode / പേയ്മെന്റ് രീതി <span class="text-rose-500">*</span>
                    </label>
                    <select name="payment_method" x-model="paymentMethod" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:border-emerald-500 text-sm font-semibold text-slate-800">
                        <option value="cash">Cash (പണം)</option>
                        <option value="upi">UPI / GPay / PhonePe</option>
                        <option value="bank_transfer">Bank Transfer / NEFT</option>
                        <option value="card">Debit / Credit Card</option>
                        <option value="other">Other Mode</option>
                    </select>
                </div>

                <!-- Category / Purpose -->
                <div class="space-y-1.5 md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Purpose / വിഭാഗം
                    </label>
                    <select name="category" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:border-emerald-500 text-sm font-semibold text-slate-800">
                        <option value="Festival Fund">Festival Fund (ഫെസ്റ്റിവൽ ഫണ്ട്)</option>
                        <option value="Fest Sponsorship">Fest Sponsorship (സ്പോൺസർഷിപ്പ്)</option>
                    </select>
                </div>

                <!-- Notes -->
                <div class="space-y-1.5 md:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Notes / കുറിപ്പ് (Optional)
                    </label>
                    <input type="text" name="notes" value="{{ old('notes') }}"
                           placeholder="Any reference or memo to print on voucher"
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:border-emerald-500 text-sm text-slate-800 placeholder-slate-400">
                </div>
            </div>

            <!-- Submit Action -->
            <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Receipt will immediately open in printable voucher layout with verification code.</span>
                </div>

                <button type="submit" 
                        class="px-8 py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm uppercase tracking-wider shadow-lg shadow-emerald-600/30 hover:shadow-emerald-600/40 transition-all flex items-center justify-center gap-2">
                    <span>Generate & Print Receipt</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Receipts Ledger Table -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
        <!-- Table Header & Search -->
        <div class="px-6 py-4 border-b border-slate-200/80 bg-slate-50/70 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 tracking-tight">Receipts Ledger & History</h3>
                <p class="text-xs text-slate-500">Recently issued festival contribution receipts</p>
            </div>

            <form method="GET" action="{{ route('fund.dashboard') }}" class="flex items-center gap-2">
                <div class="relative w-full sm:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search donor, phone, receipt no..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-emerald-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-3.5 py-2 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition-colors shrink-0">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('fund.dashboard') }}" class="px-2.5 py-2 text-xs text-slate-500 hover:text-slate-800">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4">Receipt #</th>
                        <th class="py-3.5 px-4">Donor Name & Place</th>
                        <th class="py-3.5 px-4">Contact</th>
                        <th class="py-3.5 px-4">Amount</th>
                        <th class="py-3.5 px-4">Payment Mode</th>
                        <th class="py-3.5 px-4">Date & Time</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($receipts as $rcp)
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $rcp->is_cancelled ? 'bg-rose-50/30' : '' }}">
                            <!-- Receipt No -->
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-[11px]">
                                    {{ $rcp->receipt_number }}
                                </span>
                            </td>

                            <!-- Donor Name & Place -->
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $rcp->donor_name }}</div>
                                @if($rcp->donor_place)
                                    <div class="text-[11px] text-slate-500">{{ $rcp->donor_place }}</div>
                                @endif
                            </td>

                            <!-- Contact -->
                            <td class="py-3 px-4 font-mono text-slate-600 text-[11px]">
                                {{ $rcp->donor_phone ?: '—' }}
                            </td>

                            <!-- Amount -->
                            <td class="py-3 px-4">
                                <span class="text-sm font-black font-mono text-emerald-700">
                                    ₹{{ number_format($rcp->amount, 2) }}
                                </span>
                            </td>

                            <!-- Payment Mode -->
                            <td class="py-3 px-4">
                                @php
                                    $modeColor = match($rcp->payment_method) {
                                        'cash' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                        'upi', 'gpay' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                                        'bank_transfer' => 'bg-blue-50 text-blue-800 border-blue-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase font-mono border {{ $modeColor }}">
                                    {{ strtoupper($rcp->payment_method) }}
                                </span>
                                @if($rcp->transaction_reference)
                                    <div class="text-[9px] font-mono text-slate-400 mt-0.5 truncate max-w-[120px]" title="{{ $rcp->transaction_reference }}">
                                        Ref: {{ $rcp->transaction_reference }}
                                    </div>
                                @endif
                            </td>

                            <!-- Date & Time -->
                            <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                                <div>{{ $rcp->created_at->format('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $rcp->created_at->format('h:i A') }}</div>
                            </td>

                            <!-- Action -->
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('fund.receipts.show', $rcp) }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors">
                                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <span>Voucher</span>
                                    </a>
                                    <a href="{{ route('fund.receipts.download', $rcp) }}" 
                                       title="Download JPG Voucher"
                                       class="inline-flex items-center px-2 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold text-xs transition-colors">
                                        <span>JPG ↓</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p class="text-sm font-semibold text-slate-600">No receipts issued yet</p>
                                <p class="text-xs text-slate-400 mt-1">Use the form above to record your first contribution receipt.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receipts->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $receipts->links() }}
            </div>
        @endif

        <!-- Security Notice -->
        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200/80 flex items-center justify-between text-[11px] text-slate-500">
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>Security Notice: Receipt records and donor contributions can only be deleted by Central Admin.</span>
            </span>
            <span class="font-mono text-slate-400">QUAF-FUND-SYS-V1</span>
        </div>
    </div>
</div>

@endsection

