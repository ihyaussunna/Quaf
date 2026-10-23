@extends('layouts.public', ['title' => 'Student Fund Desk — ' . $student->name])

@section('content')
<script>
function studentFundDesk() {
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
        },
        async shareReceiptImage(imageUrl, fileName, donorName, donorPlace, receiptUrl) {
            const donorDisplayName = donorPlace ? `${donorName}, ${donorPlace}` : (donorName || '');
            const shareMessage = `ജസാഖല്ലാഹു ഖൈർ...
*${donorDisplayName}*
നിങ്ങളുടെ ഈ ഹദിയ അല്ലാഹു സ്വീകരിക്കട്ടെ... 

ഖാഫ് അദബിക് എഡിഷനിൽ ഞങ്ങളോടൊപ്പം ചേരാൻ മർകസിലേക്ക് നിങ്ങൾക്കും ഹൃദ്യമായ സ്വാഗതം`;

            try {
                if (navigator.share) {
                    const response = await fetch(imageUrl);
                    const blob = await response.blob();
                    const file = new File([blob], fileName, { type: 'image/jpeg' });
                    if (navigator.canShare && navigator.canShare({ files: [file] })) {
                        await navigator.share({
                            files: [file],
                            title: 'QUAF Receipt',
                            text: shareMessage
                        });
                        return;
                    }
                }
            } catch (e) {
                console.warn('Share error or canceled:', e);
            }

            // Fallback for desktop: download JPG and open WhatsApp
            const a = document.createElement('a');
            a.href = imageUrl;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            const fallbackText = receiptUrl ? `${shareMessage}\n\n${receiptUrl}` : shareMessage;
            window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(fallbackText), '_blank');
        },
        showEditModal: false,
        editingReceipt: {
            id: null,
            receipt_number: '',
            donor_name: '',
            payment_method: 'cash',
            transaction_reference: '',
            donor_phone: '',
            donor_place: '',
            update_url: ''
        },
        openEditModal(rcp, updateUrl) {
            this.editingReceipt = {
                id: rcp.id,
                receipt_number: rcp.receipt_number,
                donor_name: rcp.donor_name || '',
                payment_method: rcp.payment_method || 'cash',
                transaction_reference: rcp.transaction_reference || '',
                donor_phone: rcp.donor_phone || '',
                donor_place: rcp.donor_place || '',
                update_url: updateUrl
            };
            this.showEditModal = true;
        },
        showDeleteModal: false,
        deleteReason: '',
        deletingReceipt: {
            id: null,
            receipt_number: '',
            donor_name: '',
            amount: 0,
            delete_url: ''
        },
        openDeleteModal(rcp, deleteUrl) {
            this.deletingReceipt = {
                id: rcp.id,
                receipt_number: rcp.receipt_number,
                donor_name: rcp.donor_name || '',
                amount: rcp.amount || 0,
                delete_url: deleteUrl
            };
            this.deleteReason = '';
            this.showDeleteModal = true;
        }
    };
}
</script>

<div class="bg-slate-50 text-slate-900 min-h-screen py-8 sm:py-12 px-4 sm:px-6 lg:px-8 font-sora relative selection:bg-amber-100 selection:text-amber-900"
     x-data="studentFundDesk()">

    <!-- Ambient Subtle Warm Background Gradients -->
    <div class="absolute top-0 inset-x-0 h-72 bg-gradient-to-b from-slate-200/40 via-slate-100/20 to-transparent pointer-events-none"></div>

    <div class="max-w-6xl mx-auto space-y-6 sm:space-y-8 relative z-10">

        <!-- Top Navigation & Student Identity Banner (Pure White Card) -->
        <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200/90 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-5 sm:gap-6">
            
            <!-- Left: Student Info & Avatar -->
            <div class="flex items-center gap-4 min-w-0">
                <!-- Clean Student Icon Avatar (No Big Number Box) -->
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center shrink-0 shadow-xs"
                     style="background-color: {{ $student->group?->color_hex ?? '#be1e2d' }}15; color: {{ $student->group?->color_hex ?? '#be1e2d' }}; border: 1.5px solid {{ $student->group?->color_hex ?? '#be1e2d' }}35;">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Student Desk Active
                        </span>
                        @if($student->group)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold tracking-wider"
                                  style="background-color: {{ $student->group->color_hex }}15; color: {{ $student->group->color_hex }}; border: 1px solid {{ $student->group->color_hex }}35;">
                                {{ $student->group->code ? $student->group->code . ' • ' . $student->group->name : $student->group->name }}
                            </span>
                        @endif
                    </div>

                    <!-- Full Name (No Truncation) -->
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 uppercase tracking-tight mt-1 break-words leading-tight">
                        {{ $student->name }}
                    </h1>

                    <!-- Clear Chest Number & Class -->
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs font-mono text-slate-500 mt-1">
                        <span>Chest: <strong class="text-slate-900 font-bold">{{ $student->chest_number ?: $student->student_id }}</strong></span>
                        <span>•</span>
                        <span>Class: <strong class="text-slate-800">{{ $student->class_level ?: '-' }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Right: Actions -->
            <div class="flex flex-wrap items-center gap-2.5 sm:self-center shrink-0">
                <a href="{{ route('fund.home') }}" 
                   class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs uppercase tracking-wider border border-slate-200/80 transition-all shadow-2xs">
                    <span>← Fund Home</span>
                </a>
                <a href="{{ route('fund.student.logout') }}" 
                   class="px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs uppercase tracking-wider border border-rose-200 transition-all shadow-2xs">
                    <span>Switch Student / Logout</span>
                </a>
            </div>

        </div>

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-bold flex items-center justify-between shadow-2xs">
                <span>{{ session('success') }}</span>
                <span class="text-xs font-mono text-emerald-600 font-bold">Success</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-bold shadow-2xs">
                {{ session('error') }}
            </div>
        @endif

        <!-- Personal Metric Cards (Pure White Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Total Collected -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/90 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-500">My Total Collection</div>
                    <div class="text-2xl sm:text-3xl font-black font-mono text-emerald-600 mt-1">
                        ₹{{ number_format($myTotalCollected, 0) }}
                    </div>
                    <div class="text-[10px] text-slate-400 font-anek mt-0.5">താങ്കൾ ഇതുവരെ സ്വീകരിച്ച തുക</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black font-mono text-xl border border-emerald-100 shrink-0">
                    ₹
                </div>
            </div>

            <!-- Total Receipts -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/90 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-500">Receipts Issued</div>
                    <div class="text-2xl sm:text-3xl font-black font-mono text-amber-600 mt-1">
                        {{ number_format($myReceiptsCount) }}
                    </div>
                    <div class="text-[10px] text-slate-400 font-anek mt-0.5">നൽകിയ ആകെ രസീതുകൾ</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold border border-amber-100 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Issue Receipt Form Card (Clean White Design) -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden" id="new-receipt-card">
            
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-bold shadow-xs shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-slate-900 tracking-tight">Issue New Receipt (പുതിയ രസീത് നൽകുക)</h2>
                        <p class="text-xs text-slate-500 font-anek">വിവരങ്ങൾ പൂരിപ്പിച്ച് രസീത് ജനറേറ്റ് ചെയ്യുക</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('fund.student.receipts.store') }}" class="p-6 sm:p-8 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Donor Name -->
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                            Donor Name / ദാതാവിന്റെ പേര് <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="donor_name" value="{{ old('donor_name') }}" required
                               placeholder="ദാതാവിന്റെ പേര് (ഇംഗ്ലീഷിലോ മലയാളത്തിലോ നൽകാം)"
                               style="font-family: 'Sora', 'Anek Malayalam', sans-serif;"
                               class="w-full px-4 py-3 rounded-xl bg-slate-50/80 hover:bg-slate-100/70 focus:bg-white border border-slate-200 text-slate-900 placeholder-slate-400 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                        @error('donor_name')
                            <p class="text-xs text-rose-500 font-mono mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Donor Phone -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                            Mobile Number / ഫോൺ നമ്പർ
                        </label>
                        <input type="tel" name="donor_phone" value="{{ old('donor_phone') }}"
                               placeholder="WhatsApp / Phone number"
                               class="w-full px-4 py-3 rounded-xl bg-slate-50/80 hover:bg-slate-100/70 focus:bg-white border border-slate-200 text-slate-900 placeholder-slate-400 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                        @error('donor_phone')
                            <p class="text-xs text-rose-500 font-mono mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Donor Place -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                            Place / സ്ഥലം
                        </label>
                        <input type="text" name="donor_place" value="{{ old('donor_place') }}"
                               placeholder="e.g. Kozhikode, Karanthur"
                               class="w-full px-4 py-3 rounded-xl bg-slate-50/80 hover:bg-slate-100/70 focus:bg-white border border-slate-200 text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                    </div>

                    <!-- Amount -->
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                            Contribution Amount (INR) / സംഭാവന തുക <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base font-black font-mono text-amber-600">
                                ₹
                            </span>
                            <input type="number" step="1" min="1" max="10000000" name="amount" x-model.number="amount" required
                                   placeholder="500"
                                   class="w-full pl-9 pr-4 py-3 rounded-xl bg-slate-50/80 hover:bg-slate-100/70 focus:bg-white border border-slate-200 text-slate-900 placeholder-slate-400 text-lg font-black font-mono focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                        </div>
                        @error('amount')
                            <p class="text-xs text-rose-500 font-mono mt-1">{{ $message }}</p>
                        @enderror

                        <!-- Quick Amount Chips -->
                        <div class="flex flex-wrap items-center gap-2 pt-2">
                            <span class="text-[11px] font-bold text-slate-500 uppercase font-mono mr-1">Quick Select:</span>
                            <template x-for="val in [500, 1000, 2000, 5000, 10000, 25000]" :key="val">
                                <button type="button" @click="amount = val" 
                                        :class="amount === val ? 'bg-amber-400 text-slate-950 font-bold border-amber-400 shadow-2xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200 font-semibold'"
                                        class="px-2.5 py-1 text-xs rounded-lg border font-mono transition-colors cursor-pointer">
                                    ₹<span x-text="val.toLocaleString('en-IN')"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Live In-Words Preview -->
                        <div class="p-2.5 rounded-xl bg-amber-50/70 border border-amber-200/80 text-xs font-mono text-slate-700 flex items-center gap-2 mt-2" x-show="amount > 0">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700">In Words:</span>
                            <span class="font-bold text-slate-900" x-text="convertAmountToWords(amount)"></span>
                        </div>
                    </div>

                    <!-- Payment Mode -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                            Payment Mode / പേയ്മെന്റ് രീതി <span class="text-rose-500">*</span>
                        </label>
                        <select name="payment_method" x-model="paymentMethod" required
                                class="w-full px-4 py-3 rounded-xl bg-slate-50/80 hover:bg-slate-100/70 focus:bg-white border border-slate-200 text-slate-900 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                            <option value="cash">Cash (പണം)</option>
                            <option value="upi">UPI / GPay / PhonePe</option>
                            <option value="bank_transfer">Bank Transfer / NEFT</option>
                            <option value="card">Debit / Credit Card</option>
                            <option value="other">Other Mode</option>
                        </select>
                    </div>

                    <!-- Purpose / Category (Strictly Festival Fund / Fest Sponsorship only) -->
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                            Purpose / വിഭാഗം
                        </label>
                        <select name="category" 
                                class="w-full px-4 py-3 rounded-xl bg-slate-50/80 hover:bg-slate-100/70 focus:bg-white border border-slate-200 text-slate-900 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                            <option value="Festival Fund">Festival Fund (ഫെസ്റ്റിവൽ ഫണ്ട്)</option>
                            <option value="Fest Sponsorship">Fest Sponsorship (സ്പോൺസർഷിപ്പ്)</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="space-y-1.5 md:col-span-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                            Notes / കുറിപ്പ് (Optional)
                        </label>
                        <input type="text" name="notes" value="{{ old('notes') }}"
                               placeholder="Any reference note"
                               class="w-full px-4 py-3 rounded-xl bg-slate-50/80 hover:bg-slate-100/70 focus:bg-white border border-slate-200 text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                    </div>

                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="text-xs text-slate-500 font-anek">
                        രസീത് സേവ് ചെയ്ത ഉടൻ വൗച്ചറും വാട്ട്‌സ്ആപ്പ് ഷെയറിംഗ് ഓപ്ഷനും ലഭിക്കും.
                    </div>

                    <button type="submit" 
                            class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-amber-500 via-amber-400 to-amber-500 hover:brightness-105 text-slate-950 font-black text-sm uppercase tracking-wider shadow-lg shadow-amber-500/20 transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-98">
                        <span>Generate & View Receipt</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>

            </form>
        </div>

        <!-- My Receipts Ledger Table (Clean White Card) -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">My Issued Receipts</h2>
                    <p class="text-xs text-slate-500 font-anek">താങ്കൾ വിതരണം ചെയ്ത രസീതുകളുടെ പട്ടിക</p>
                </div>
                <span class="text-xs font-mono text-slate-500">Total: {{ $myReceiptsCount }} entries</span>
            </div>

            @if($myRecentReceipts->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-mono text-[10px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3.5">Receipt #</th>
                                <th class="px-5 py-3.5">Donor Name & Place</th>
                                <th class="px-5 py-3.5">Amount</th>
                                <th class="px-5 py-3.5">Payment</th>
                                <th class="px-5 py-3.5">Date</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-mono">
                            @foreach($myRecentReceipts as $rcp)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-5 py-4 font-bold text-amber-700">
                                        {{ $rcp->receipt_number }}
                                    </td>
                                    <td class="px-5 py-4 font-sora">
                                        <div class="font-bold text-slate-900 uppercase text-xs break-words">{{ $rcp->donor_name }}</div>
                                        @if($rcp->donor_place)
                                            <div class="text-[11px] text-slate-500">{{ $rcp->donor_place }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 font-black text-emerald-600 text-sm">
                                        ₹{{ number_format($rcp->amount, 0) }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $rcp->payment_method }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-slate-500 text-[11px]">
                                        {{ $rcp->created_at->format('d/m/Y h:i A') }}
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Edit Receipt Button -->
                                            <button type="button" 
                                                    @click="openEditModal({
                                                        id: {{ $rcp->id }},
                                                        receipt_number: '{{ $rcp->receipt_number }}',
                                                        donor_name: @js($rcp->donor_name),
                                                        payment_method: '{{ $rcp->payment_method }}',
                                                        transaction_reference: @js($rcp->transaction_reference ?? ''),
                                                        donor_phone: @js($rcp->donor_phone ?? ''),
                                                        donor_place: @js($rcp->donor_place ?? '')
                                                    }, '{{ route('fund.student.receipts.update', $rcp) }}')"
                                                    class="px-2.5 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-[11px] border border-blue-200 transition-all shadow-2xs cursor-pointer flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                <span>Edit</span>
                                            </button>

                                            <!-- Delete Receipt Button -->
                                            <button type="button" 
                                                    @click="openDeleteModal({
                                                        id: {{ $rcp->id }},
                                                        receipt_number: '{{ $rcp->receipt_number }}',
                                                        donor_name: @js($rcp->donor_name),
                                                        amount: {{ $rcp->amount }}
                                                    }, '{{ route('fund.student.receipts.destroy', $rcp) }}')"
                                                    class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-[11px] border border-rose-200 transition-all shadow-2xs cursor-pointer flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Delete</span>
                                            </button>

                                            <!-- View Voucher -->
                                            <a href="{{ route('fund.receipts.show', $rcp) }}" 
                                               class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-[11px] border border-slate-200 transition-all shadow-2xs">
                                                View
                                            </a>

                                            <!-- Download JPG -->
                                            <a href="{{ route('fund.receipts.download', $rcp) }}" 
                                               class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-[11px] border border-amber-200 transition-all shadow-2xs">
                                                JPG
                                            </a>

                                            <!-- WhatsApp Image Share Button -->
                                            <button type="button" 
                                                    @click="shareReceiptImage('{{ route('fund.receipts.image', $rcp) }}', '{{ $rcp->receipt_number }}.jpg', @js($rcp->donor_name), @js($rcp->donor_place ?? ''), '{{ route('fund.receipts.show', $rcp) }}')"
                                                    class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition-all cursor-pointer flex items-center gap-1 shadow-xs">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                                <span>Share</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $myRecentReceipts->links() }}
                </div>
            @else
                <div class="py-12 text-center text-slate-400 font-anek text-xs">
                    നിങ്ങൾ ഇതുവരെ രസീതുകൾ നൽകിയിട്ടില്ല. മുകളിലെ ഫോം ഉപയോഗിച്ച് ആദ്യത്തെ രസീത് നൽകുക.
                </div>
            @endif
        </div>

    </div>

    <!-- =====================================================================
         STUDENT RECEIPT EDIT MODAL (DONOR NAME & PAYMENT METHOD)
         ===================================================================== -->
    <div x-show="showEditModal" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/80 backdrop-blur-md"
         style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; height: 100dvh !important; z-index: 99999 !important; display: flex !important; align-items: center !important; justify-content: center !important; background-color: rgba(10, 15, 30, 0.8) !important; -webkit-backdrop-filter: blur(8px) !important; backdrop-filter: blur(8px) !important;"
         @keydown.escape.window="showEditModal = false">
        
        <!-- Backdrop Click to Close -->
        <div class="fixed inset-0" style="position: absolute; inset: 0; width: 100%; height: 100%;" @click="showEditModal = false"></div>

        <!-- Pure White Apple-Style Dialog Card -->
        <div x-show="showEditModal"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
             x-transition:enter-start="opacity-0 translate-y-6 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-6 scale-95"
             class="relative w-full max-w-lg bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100 z-10 text-slate-900 space-y-5"
             style="position: relative !important; z-index: 100000 !important; background-color: #ffffff !important; color: #0f172a !important; border-radius: 24px !important; box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.45) !important; max-height: calc(100dvh - 32px) !important; overflow-y: auto !important;"
             @click.stop>
            
            <!-- Apple-Style Circular Close Button -->
            <button type="button" 
                    @click="showEditModal = false"
                    class="absolute top-5 right-5 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-800 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <!-- Modal Header -->
            <div class="flex items-center gap-3.5 pr-8">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-200 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-blue-50 text-blue-800 border border-blue-200">
                            Edit Receipt
                        </span>
                        <span class="text-xs font-mono font-bold text-slate-500" x-text="'#' + editingReceipt.receipt_number"></span>
                    </div>
                    <h3 class="font-sora text-lg sm:text-xl font-bold text-slate-900 tracking-tight mt-0.5">
                        രസീത് വിവരങ്ങൾ തിരുത്തുക
                    </h3>
                </div>
            </div>

            <!-- Edit Form -->
            <form method="POST" :action="editingReceipt.update_url" class="space-y-4 pt-1">
                @csrf
                @method('PUT')

                <!-- 1. Donor Name (Malayalam / English) -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 font-mono">
                        Donor Name / ദാതാവിന്റെ പേര് <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="donor_name" 
                           x-model="editingReceipt.donor_name" 
                           required
                           placeholder="Enter name (e.g. മുഹമ്മദ് ഷാഫി / Mohammed Shafi)"
                           style="font-family: 'Sora', 'Anek Malayalam', sans-serif;"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 focus:bg-white border border-slate-200 text-sm font-semibold text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                </div>

                <!-- 2. Payment Method (Cash, UPI, etc.) -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 font-mono">
                        Payment Mode / പേയ്മെന്റ് രീതി <span class="text-rose-500">*</span>
                    </label>
                    <select name="payment_method" 
                            x-model="editingReceipt.payment_method" 
                            required
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 focus:bg-white border border-slate-200 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                        <option value="upi">UPI / GPay / PhonePe (യു.പി.ഐ)</option>
                        <option value="cash">Cash (പണം)</option>
                        <option value="bank_transfer">Bank Transfer / NEFT</option>
                        <option value="card">Debit / Credit Card</option>
                        <option value="other">Other Mode (മറ്റുള്ളവ)</option>
                    </select>
                </div>

                <!-- 3. Transaction Reference (Shown prominently if UPI or other non-cash) -->
                <div class="space-y-1" x-show="editingReceipt.payment_method !== 'cash'">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 font-mono">
                        UPI Reference / Transaction ID
                    </label>
                    <input type="text" 
                           name="transaction_reference" 
                           x-model="editingReceipt.transaction_reference" 
                           placeholder="e.g. UPI Ref, GPay Txn ID, UTR"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 focus:bg-white border border-slate-200 text-sm font-mono text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                </div>

                <!-- 4. Phone & Place (Optional) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 font-mono">
                            Mobile Number
                        </label>
                        <input type="tel" 
                               name="donor_phone" 
                               x-model="editingReceipt.donor_phone" 
                               placeholder="Phone number"
                               class="w-full px-3.5 py-2 rounded-xl bg-slate-50 focus:bg-white border border-slate-200 text-xs font-mono text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 font-mono">
                            Place / സ്ഥലം
                        </label>
                        <input type="text" 
                               name="donor_place" 
                               x-model="editingReceipt.donor_place" 
                               placeholder="e.g. Kozhikode"
                               class="w-full px-3.5 py-2 rounded-xl bg-slate-50 focus:bg-white border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" 
                            @click="showEditModal = false"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs uppercase tracking-wider transition-all cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-xs uppercase tracking-wider transition-all shadow-md shadow-blue-600/20 cursor-pointer active:scale-98">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- =====================================================================
         STUDENT RECEIPT DELETION / CANCELLATION MODAL
         ===================================================================== -->
    <div x-show="showDeleteModal" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/80 backdrop-blur-md"
         style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; height: 100dvh !important; z-index: 99999 !important; display: flex !important; align-items: center !important; justify-content: center !important; background-color: rgba(10, 15, 30, 0.8) !important; -webkit-backdrop-filter: blur(8px) !important; backdrop-filter: blur(8px) !important;"
         @keydown.escape.window="showDeleteModal = false">
        
        <div class="fixed inset-0" style="position: absolute; inset: 0; width: 100%; height: 100%;" @click="showDeleteModal = false"></div>

        <div x-show="showDeleteModal"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
             x-transition:enter-start="opacity-0 translate-y-6 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-6 scale-95"
             class="relative w-full max-w-md bg-white rounded-3xl p-6 sm:p-7 shadow-2xl border border-slate-100 z-10 text-slate-900 space-y-4"
             style="position: relative !important; z-index: 100000 !important; background-color: #ffffff !important; color: #0f172a !important; border-radius: 24px !important; box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.45) !important;"
             @click.stop>
            
            <button type="button" 
                    @click="showDeleteModal = false"
                    class="absolute top-5 right-5 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-800 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="flex items-center gap-3 pr-8">
                <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="font-sora text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                        രസീത് റദ്ദാക്കുക / ഡിലീറ്റ് ചെയ്യുക
                    </h3>
                    <p class="text-xs text-slate-500 font-anek">ഈ രസീത് സിസ്റ്റത്തിൽ നിന്നും റദ്ദാക്കപ്പെടുന്നു.</p>
                </div>
            </div>

            <!-- Receipt Info Card -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-1 font-mono">
                <div>Receipt: <strong class="text-amber-800" x-text="'#' + deletingReceipt.receipt_number"></strong></div>
                <div>Donor: <strong class="text-slate-900" x-text="deletingReceipt.donor_name"></strong></div>
                <div>Amount: <strong class="text-emerald-700">₹<span x-text="Number(deletingReceipt.amount).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span></strong></div>
            </div>

            <!-- Delete Form -->
            <form method="POST" :action="deletingReceipt.delete_url" class="space-y-4 pt-1">
                @csrf
                @method('DELETE')

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1 font-mono">
                        Reason for Cancellation / റദ്ദാക്കാനുള്ള കാരണം <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="reason" 
                           x-model="deleteReason" 
                           required
                           placeholder="ഉദാ: തുക തെറ്റായി രേഖപ്പെടുത്തി, ഡ്യൂപ്ലിക്കേറ്റ്, ദാതാവ് റദ്ദാക്കി..."
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 font-medium">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" 
                            @click="showDeleteModal = false" 
                            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="!deleteReason.trim()"
                            :class="deleteReason.trim() ? 'bg-rose-600 hover:bg-rose-700 text-white cursor-pointer shadow-rose-600/20' : 'bg-rose-300 text-white cursor-not-allowed'"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-md active:scale-98">
                        Confirm & Delete Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
