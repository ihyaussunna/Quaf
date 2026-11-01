@extends('layouts.public', ['title' => 'Participant Verification — QUAF'])

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8 py-8 sm:py-16 space-y-6 sm:space-y-8 pb-28 sm:pb-16 overflow-hidden sm:overflow-visible">
    
    <div class="text-center px-2">
        <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">PARTICIPANT IDENTIFICATION & WALLET</span>
        <h1 class="text-2xl sm:text-4xl font-sora font-black text-slate-900 mt-1">Official Delegate Profile</h1>
    </div>

    @if($isValid && $student)
        <!-- Valid Student Profile -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-4 sm:p-8 md:p-10 shadow-lg space-y-6 sm:space-y-8 overflow-hidden w-full max-w-full">
            
            <!-- Student Header Profile -->
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 sm:gap-6 pb-6 border-b border-slate-200 w-full max-w-full">
                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden bg-slate-100 border-2 border-slate-200 shadow-md shrink-0 flex items-center justify-center">
                    @if($student->photo_url)
                        <img src="{{ $student->photo_url }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                    @elseif($student->photo_path)
                        <img src="{{ asset('storage/' . $student->photo_path) }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center bg-slate-50 text-slate-400 font-black text-3xl">
                            {{ strtoupper(substr($student->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                <div class="w-full flex-1 text-center sm:text-left min-w-0 max-w-full overflow-hidden">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-1.5 sm:gap-2 mb-2 w-full">
                        <span class="inline-flex items-center px-2.5 sm:px-3 py-1 rounded-lg text-[11px] sm:text-xs font-mono font-bold bg-red-50 text-[#be1e2d] border border-red-200">
                            Chest #: {{ $student->chest_number ?? $student->student_id }}
                        </span>
                        <span class="inline-flex items-center px-2.5 sm:px-3 py-1 rounded-lg text-[11px] sm:text-xs font-mono font-bold" style="background-color: {{ $student->group?->color_hex ?? '#2e3192' }}20; color: {{ $student->group?->color_hex ?? '#2e3192' }}">
                            House: {{ $student->group?->name ?? 'QUAF' }}
                        </span>
                        <span class="inline-flex items-center px-2 sm:px-2.5 py-1 rounded-lg text-[11px] sm:text-xs font-mono bg-slate-100 text-slate-700">
                            {{ $student->zone_name }}
                        </span>
                    </div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-sora font-black text-slate-900 break-words leading-tight w-full">{{ $student->name }}</h2>
                    <div class="text-xs font-mono text-slate-500 space-y-0.5 mt-1.5">
                        <p>Class / Category: <strong class="text-slate-700">{{ $student->class_level ?? $student->category }}</strong></p>
                        <p>Festival Contribution: <strong class="text-[#be1e2d] font-bold">{{ $publicPoints ?? $student->public_points ?? 0 }} Points</strong></p>
                    </div>
                </div>
            </div>

            <!-- RESULT ADJUSTMENT & CLAWBACK NOTICE BANNER -->
            @if(!empty($clawbackSummary['has_purchase_with_clawback']) || ($coinSummary['excess_spent'] ?? 0) > 0 || !empty($clawbackSummary['adjustments']))
                <div class="p-4 sm:p-5 rounded-2xl bg-rose-50 border-2 border-rose-300 text-rose-950 space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs sm:text-sm font-black font-sora text-rose-950 flex items-center gap-2 flex-wrap">
                                <span>ഫലം തിരുത്തൽ / കോയിൻ ക്രമീകരണം (Result Correction Alert)</span>
                                @if(!empty($clawbackSummary['has_purchase_with_clawback']))
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-black bg-rose-200 text-rose-900 border border-rose-300">
                                        ആദ്യ ഫലപ്രകാരം സാധനങ്ങൾ കൈപ്പറ്റിയിട്ടുണ്ട്
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-rose-800 font-medium mt-1 leading-relaxed">
                                @if(!empty($clawbackSummary['has_purchase_with_clawback']))
                                    ആദ്യ ഫലപ്രഖ്യാപനപ്രകാരമുള്ള കോയിൻ ഉപയോഗിച്ച് പ്രസന്റേഷൻ ഡെസ്കിൽ നിന്ന് സാധനങ്ങൾ കൈപ്പറ്റിയ ശേഷം ചില മത്സരങ്ങളുടെ ഫലത്തിൽ ഔദ്യോഗികമായി മാറ്റം വരികയും (Judgment Corrected) കോയിനുകൾ പിൻവലിക്കുകയും ചെയ്തിട്ടുണ്ട്.
                                @else
                                    മത്സരഫലം പുനഃപരിശോധിച്ചതിലൂടെ ഫലത്തിൽ മാറ്റം വരികയും അർഹമായ കോയിനുകൾ ക്രമീകരിക്കുകയും ചെയ്തിട്ടുണ്ട്.
                                @endif
                            </p>
                        </div>
                    </div>

                    @if(($coinSummary['excess_spent'] ?? 0) > 0)
                        <div class="p-3 rounded-xl bg-white border border-rose-200 flex items-center justify-between text-xs font-mono font-bold shadow-2xs">
                            <div>
                                <span class="text-rose-700 block">ആദ്യ ഫലപ്രകാരം അധികമായി കൈപ്പറ്റിയത് (Over-redeemed / Excess Spent):</span>
                                <span class="text-[10px] text-slate-500 font-normal">ആദ്യ റിസൾട്ട് അനുസരിച്ച് സാധനങ്ങൾ നൽകിയതിലൂടെ ഉണ്ടായ കുറവ്</span>
                            </div>
                            <span class="text-rose-700 font-black text-sm shrink-0">-{{ number_format($coinSummary['excess_spent']) }} Coins</span>
                        </div>
                    @endif

                    @if(!empty($clawbackSummary['adjustments']))
                        <div class="pt-2 border-t border-rose-200/80 space-y-1.5">
                            <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-rose-800 block">തിരുത്തിയ മത്സരങ്ങളുടെ വിവരങ്ങൾ (Adjusted Competitions):</span>
                            @foreach($clawbackSummary['adjustments'] as $adj)
                                <div class="p-2.5 rounded-xl bg-white/80 border border-rose-200/80 text-xs flex items-center justify-between gap-2 shadow-2xs">
                                    <div class="min-w-0">
                                        <strong class="text-slate-900 block truncate">{{ $adj['program_name'] }}</strong>
                                        <span class="text-rose-800 text-[11px] font-anek">{{ $adj['description'] }}</span>
                                    </div>
                                    <span class="font-mono font-black text-rose-700 shrink-0 text-xs">-{{ $adj['clawback_amount'] }} Coins</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <!-- COIN WALLET BALANCE GRID -->
            @if($coinSummary)
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    <!-- Available Balance -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-amber-500 via-amber-400 to-yellow-500 text-slate-950 shadow-md border border-amber-300 col-span-2 sm:col-span-1">
                        <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-800">Wallet Balance</div>
                        <div class="text-2xl sm:text-3xl font-black mt-1 font-sora flex items-baseline gap-1.5">
                            <span>{{ number_format($coinSummary['balance']) }}</span>
                            <span class="text-[10px] font-mono uppercase font-bold text-slate-800">Coins</span>
                        </div>
                    </div>

                    <!-- Competition Earned -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">മത്സരത്തിലൂടെ നേടിയത്</div>
                        <div class="text-xl sm:text-2xl font-black mt-1 font-sora text-amber-700">
                            +{{ number_format($coinSummary['competition_earned'] ?? $coinSummary['total_earned']) }}
                        </div>
                        <span class="text-[9px] text-slate-400 font-mono">Earned Coins</span>
                    </div>

                    <!-- Added Coins -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-emerald-50 border-2 border-emerald-300">
                        <div class="flex items-center justify-between">
                            <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-emerald-800">പണം നൽകി ചേർത്തത്</div>
                            <span class="px-1 py-0.2 rounded text-[8px] font-mono font-bold bg-emerald-200 text-emerald-900">ADDED COIN</span>
                        </div>
                        <div class="text-xl sm:text-2xl font-black mt-1 font-sora text-emerald-700">
                            +{{ number_format($coinSummary['added_coins'] ?? 0) }}
                        </div>
                        <span class="text-[9px] text-emerald-700 font-mono">Cashier Added</span>
                    </div>

                    <!-- Total Spent -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">Total Coins Spent</div>
                        <div class="text-xl sm:text-2xl font-black mt-1 font-sora text-rose-600">
                            -{{ number_format($coinSummary['total_spent']) }}
                        </div>
                        <span class="text-[9px] text-slate-400 font-mono">Redeemed</span>
                    </div>
                </div>
            @endif

            <!-- COMPETITION RESULTS & ACHIEVEMENTS (Only shown if student obtained a prize or grade) -->
            @if(isset($awardedPrograms) && $awardedPrograms->isNotEmpty())
                <div class="space-y-4 pt-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-400">Competition Results</span>
                        <span class="text-xs font-mono text-slate-500 font-bold">{{ $awardedPrograms->count() }} {{ \Illuminate\Support\Str::plural('Result', $awardedPrograms->count()) }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($awardedPrograms as $result)
                            <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-slate-300 transition-all flex items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    @if(!empty($result['code']))
                                        <div class="text-[11px] font-mono font-bold text-[#be1e2d]">{{ $result['code'] }}</div>
                                    @endif
                                    <h4 class="font-sora font-bold text-slate-900 text-sm truncate">{{ $result['name'] }}</h4>
                                    @if(!empty($result['category']))
                                        <span class="text-[11px] font-mono text-slate-400">{{ $result['category'] }}</span>
                                    @endif
                                </div>

                                <div class="shrink-0 flex items-center gap-1.5 flex-wrap justify-end">
                                    @if($result['position'] === 1)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-mono font-black bg-amber-400 text-slate-950 shadow-xs border border-amber-300">
                                            <svg class="w-3.5 h-3.5 text-current" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                            <span>1st Prize</span>
                                        </span>
                                    @elseif($result['position'] === 2)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-mono font-black bg-blue-500 text-white shadow-xs">
                                            <svg class="w-3.5 h-3.5 text-current" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                            <span>2nd Prize</span>
                                        </span>
                                    @elseif($result['position'] === 3)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-mono font-black bg-amber-700 text-white shadow-xs">
                                            <svg class="w-3.5 h-3.5 text-current" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                            <span>3rd Prize</span>
                                        </span>
                                    @endif

                                    @if(!empty($result['grade']) && $result['grade'] !== '-')
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-black {{ in_array($result['grade'], ['A+', 'A']) ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-800 border border-slate-200' }}">
                                            Grade {{ $result['grade'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- QUAF FESTIVAL GIFT (RECOGNITION GIFT / QUAF QALAM) -->
            @php
                $isPart = $student->isCompetitionParticipant();
                $giftTitle = $student->gift_name;
                $giftDesc = $isPart 
                    ? 'മത്സരങ്ങളിൽ പങ്കെടുത്ത വിദ്യാർത്ഥികൾക്കുള്ള ക്വാഫ് റെക്കഗ്നിഷൻ ഗിഫ്റ്റ്.' 
                    : 'ഇഹ്‌യാഉസ്സുന്ന അംഗങ്ങൾക്കുള്ള ക്വാഫ് ഖലം.';
            @endphp
            <div class="p-5 sm:p-6 rounded-2xl border transition-all {{ $student->recognition_gift_delivered ? 'bg-emerald-50 border-emerald-300 text-emerald-950' : 'bg-amber-50 border-amber-300 text-amber-950' }}">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="w-3 h-3 rounded-full {{ $student->recognition_gift_delivered ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            <span class="text-xs font-mono font-bold uppercase tracking-wider">
                                {{ $giftTitle }}
                            </span>
                            @if(!$isPart)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-200 text-amber-900 border border-amber-300">
                                    ഇഹ്‌യാഉസ്സുന്ന അംഗങ്ങൾ
                                </span>
                            @endif
                        </div>
                        
                        @if($student->recognition_gift_delivered)
                            <div class="text-lg font-black font-sora mt-1 text-emerald-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>സമ്മാനം കൈപ്പറ്റി (Gift Delivered)</span>
                            </div>
                            <div class="text-xs font-mono text-emerald-800 mt-0.5">
                                {{ $giftDesc }} • Delivered: {{ $student->recognition_gift_delivered_at?->format('d M Y, h:i A') }} • Counter: {{ $student->recognition_gift_delivered_by ?? 'Presentation Desk' }}
                            </div>
                        @else
                            <div class="text-lg font-black font-sora mt-1 text-amber-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>കൈപ്പറ്റിയിട്ടില്ല (Pending Collection)</span>
                            </div>
                            <div class="text-xs font-mono text-amber-800 mt-0.5">
                                {{ $giftDesc }} പ്രസന്റേഷൻ കൗണ്ടറിൽ ക്യുആർ കോഡ് കാണിച്ച് സമ്മാനം കൈപ്പറ്റുക.
                            </div>
                        @endif
                    </div>

                    @if(auth()->check() && (auth()->user()->isAdmin() || in_array(auth()->user()->role, ['presentation_team', 'presentation', 'presentation_cashier', 'cashier'], true)))
                        <form method="POST" action="{{ route('presentation.toggle-gift', $student->id) }}" class="shrink-0">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2.5 rounded-xl font-mono font-bold text-xs uppercase tracking-wider transition-all cursor-pointer shadow-sm {{ $student->recognition_gift_delivered ? 'bg-slate-200 hover:bg-slate-300 text-slate-800' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-600/30' }}">
                                {{ $student->recognition_gift_delivered ? 'Cancel / Revert Status' : 'Mark Gift Delivered (സമ്മാനം നൽകി)' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- PURCHASE HISTORY & REDEEMED ITEMS -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-sora font-bold text-lg text-slate-900">Purchase History & Gifts</h3>
                        <p class="text-xs text-slate-500 font-mono">Record of items and rewards redeemed at the Presentation Desk.</p>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400 uppercase">
                        {{ $purchaseHistory->count() }} Items
                    </span>
                </div>

                @if($purchaseHistory->isNotEmpty())
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-500 font-mono uppercase text-[11px] bg-slate-50">
                                    <th class="py-3 px-4">Date & Time</th>
                                    <th class="py-3 px-4">Item Details</th>
                                    <th class="py-3 px-4 text-center">Coins Deducted</th>
                                    <th class="py-3 px-4 text-center">Receipt Slip</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($purchaseHistory as $purchase)
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="py-3 px-4 font-mono text-slate-500">
                                             {{ $purchase->created_at?->format('d M Y, h:i A') }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-900">{{ $purchase->description }}</div>
                                            <div class="text-[10px] font-mono text-slate-400">Ref: {{ $purchase->transaction_reference }}</div>
                                            @if(!empty($purchase->metadata['notes']))
                                                <div class="text-[10px] text-slate-500 italic">Notes: {{ $purchase->metadata['notes'] }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center font-mono font-black text-rose-600 text-sm">
                                            {{ $purchase->coins }} Coins
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <a href="{{ route('presentation.receipt', $purchase->id) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono font-bold text-[10px] uppercase transition-all inline-block">
                                                Receipt
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6 text-center text-xs text-slate-400 font-mono bg-slate-50 rounded-2xl border border-slate-200/80">
                        No purchases or redeemed items recorded yet.
                    </div>
                @endif
            </div>

            <!-- Registered Programs & Stage Status -->
            <div class="space-y-4 pt-4 border-t border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-sora font-bold text-lg text-slate-900">Allocated Program Registrations &amp; Results</h3>
                        <p class="text-xs text-slate-500 font-mono">മത്സരിച്ച മത്സരങ്ങളും അവയുടെ ഫലങ്ങളും (Enrolled Events &amp; Results)</p>
                    </div>
                    @php
                        $programList = (isset($allPrograms) && $allPrograms->isNotEmpty())
                            ? $allPrograms
                            : $student->entries->merge($student->participations)->unique('id');
                    @endphp
                    <span class="text-xs font-mono font-bold text-slate-400 uppercase">
                        {{ $programList->count() }} {{ \Illuminate\Support\Str::plural('Event', $programList->count()) }}
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($programList as $entry)
                        @php
                            $prog = $entry->program;
                            $isGroupProg = $prog ? $prog->isGroup() : false;
                            $declaredStatuses = ['send', 'delivered', 'announced', 'published'];
                            $res = $prog?->result ?? ($prog ? \App\Models\Result::where('program_id', $prog->id)->first() : null);
                            $isDeclared = $res && $res->status === 'published' && (bool) $res->is_media_published;
                            $entryAward = isset($awardedPrograms) ? $awardedPrograms->firstWhere('program_id', $entry->program_id) : null;
                        @endphp
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 text-xs font-mono text-[#be1e2d] mb-1 font-semibold flex-wrap">
                                    <span class="font-bold">{{ $prog?->code }}</span>
                                    @if($entry->chest_number)
                                        <span>•</span>
                                        <span>Chest #{{ $entry->chest_number }}</span>
                                    @endif
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase">
                                        {{ $prog?->zone?->name ?? $prog?->eligibility ?? 'All Zones' }} • {{ ucfirst($prog?->type ?? 'Individual') }}
                                    </span>
                                    @if($isGroupProg)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            ഗ്രൂപ്പ് ഇനം
                                        </span>
                                    @endif
                                </div>
                                <h4 class="font-sora font-bold text-slate-900 text-base">{{ $prog?->name }}</h4>
                                <span class="text-xs text-slate-500 font-mono">{{ $prog?->stage?->name ?? 'Stage Schedule Pending' }}</span>
                            </div>

                            <div class="shrink-0 flex items-center gap-1.5 flex-wrap justify-end">
                                @if($entryAward && ($entryAward['position'] || $entryAward['grade']))
                                    @if($entryAward['position'] === 1)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-mono font-black bg-amber-400 text-slate-950 shadow-xs border border-amber-300">
                                            <svg class="w-3.5 h-3.5 text-current" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                            <span>1st Prize</span>
                                        </span>
                                    @elseif($entryAward['position'] === 2)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-mono font-black bg-blue-500 text-white shadow-xs">
                                            <svg class="w-3.5 h-3.5 text-current" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                            <span>2nd Prize</span>
                                        </span>
                                    @elseif($entryAward['position'] === 3)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-mono font-black bg-amber-700 text-white shadow-xs">
                                            <svg class="w-3.5 h-3.5 text-current" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                            <span>3rd Prize</span>
                                        </span>
                                    @endif

                                    @if(!empty($entryAward['grade']) && $entryAward['grade'] !== '-')
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-black {{ in_array($entryAward['grade'], ['A+', 'A']) ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-800 border border-slate-200' }}">
                                            Grade {{ $entryAward['grade'] }}
                                        </span>
                                    @endif
                                @elseif($isDeclared)
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        ഫലം പ്രഖ്യാപിച്ചു (പങ്കെടുത്തു)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-medium bg-amber-50 text-amber-800 border border-amber-200">
                                        ഫലം വരാനുണ്ട് (Pending)
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 font-mono text-xs">No programs currently registered for this candidate.</p>
                    @endforelse
                </div>
            </div>

        </div>
    @else
        <!-- Invalid Student Delegate State -->
        <div class="rounded-3xl bg-white border border-red-200 p-8 sm:p-12 text-center shadow-lg max-w-lg mx-auto space-y-4">
            <div class="w-14 h-14 rounded-full bg-red-50 text-red-600 flex items-center justify-center mx-auto mb-2 border border-red-200">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-red-100 text-red-800 uppercase inline-block">
                INVALID DELEGATE PASS
            </span>
            <h2 class="text-2xl font-sora font-black text-slate-900">Student Not Found</h2>
            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                The QR identifier <strong class="font-mono text-slate-900">{{ $qrToken }}</strong> could not be matched with any accredited delegate profile.
            </p>
            <div class="pt-4">
                <a href="{{ route('verify.index') }}" class="inline-block px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition-colors">
                    Back to Verification Hub
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
