@extends('layouts.student', ['title' => 'Student Dashboard'])

@section('content')
<div class="space-y-8">
    <!-- Student Profile Hero (Standard Executive Card) -->
    <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden shadow-sm hover:shadow-md transition-shadow">
        <!-- Subtle Ambient Theme Red Aura in the background -->
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-gradient-to-br from-[#be1e2d]/8 to-transparent rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-48 h-48 bg-gradient-to-tr from-slate-100 to-transparent rounded-full blur-xl pointer-events-none"></div>

        <div class="flex items-center gap-6 relative z-10">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-red-50 to-slate-100 border-2 border-red-200 ring-4 ring-red-50 flex items-center justify-center font-sora text-3xl font-bold text-[#be1e2d] overflow-hidden flex-shrink-0 shadow-sm">
                @if($student->photo_path)
                    <img src="{{ asset('storage/' . $student->photo_path) }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                @else
                    {{ substr($student->name, 0, 1) }}
                @endif
            </div>

            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-3 py-0.5 rounded-full text-[10px] font-mono font-bold tracking-wide" style="background-color: {{ $student->group->color_hex }}18; color: {{ $student->group->color_hex }}; border: 1px solid {{ $student->group->color_hex }}30;">
                        {{ $student->group->name }}
                    </span>
                    <span class="text-[11px] font-mono font-medium text-slate-500">• {{ $student->zone_name }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-sora font-black tracking-tight text-slate-900">{{ $student->name }}</h1>
                <p class="text-xs font-mono text-slate-600 flex items-center gap-2 flex-wrap">
                    <span>Chest #: <strong class="text-[#be1e2d] font-bold text-sm">#{{ $student->chest_number ?? $student->student_id ?? '---' }}</strong></span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 relative z-10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="app-tap px-4 py-2.5 bg-slate-100 hover:bg-red-50 hover:text-red-700 text-slate-700 font-mono font-bold text-xs uppercase rounded-xl border border-slate-200 transition-all cursor-pointer">
                    Sign Out
                </button>
            </form>
        </div>
    </div>

    <!-- Official QUAF Coin Wallet Balance Grid -->
    @if($coinSummary)
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-7 shadow-sm space-y-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <h3 class="text-sm font-mono font-bold uppercase tracking-wider text-slate-900">QUAF Coin Wallet</h3>
                </div>
                <span class="text-xs font-mono text-slate-500 font-semibold px-3 py-1 bg-amber-50 text-amber-900 rounded-full border border-amber-200">
                    Live Balance Sync
                </span>
            </div>

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
                    <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-500">മത്സരത്തിലൂടെ നേടിയത്</div>
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
                    <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-500">Total Coins Spent</div>
                    <div class="text-xl sm:text-2xl font-black mt-1 font-sora text-rose-600">
                        -{{ number_format($coinSummary['total_spent']) }}
                    </div>
                    <span class="text-[9px] text-slate-400 font-mono">Redeemed</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Official Festival Gift Card (QUAF Qalam for Non-Participants / QUAF Recognition Kit for Participants) -->
    @php
        $isPart = $student->isCompetitionParticipant();
        $giftTitle = $student->gift_name;
        $giftMalayalam = $student->gift_malayalam_title;
        $chest = $student->chest_number ?? $student->student_id;
        $qrVerifyUrl = route('verify.student', $student->qr_token ?? $chest);
        $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=0&data=" . urlencode($qrVerifyUrl);
    @endphp
    <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-7 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden" x-data="{ showQrModal: false }">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="w-2.5 h-2.5 rounded-full {{ $student->recognition_gift_delivered ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse' }}"></span>
                    <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-500">
                        Festival Gift &bull; പ്രസന്റേഷൻ കൗണ്ടർ
                    </h3>
                    @if(!$isPart)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-100 text-amber-900 border border-amber-300">
                            Non-Participant Special Gift
                        </span>
                    @endif
                </div>

                <h2 class="text-xl sm:text-2xl font-black font-sora text-slate-900">
                    {{ $giftMalayalam }}
                </h2>

                <p class="text-xs text-slate-600 font-sora max-w-2xl leading-relaxed">
                    @if($isPart)
                        മത്സരങ്ങളിൽ പങ്കെടുത്ത വിദ്യാർത്ഥികൾക്ക് പ്രസന്റേഷൻ ഡെസ്കിൽ നിന്ന് ക്വാഫ് റെക്കഗ്നിഷൻ ഗിഫ്റ്റ് &amp; കിറ്റ് കൈപ്പറ്റാം.
                    @else
                        മത്സരങ്ങളിൽ പങ്കെടുക്കാത്ത വിദ്യാർത്ഥികൾക്ക് പ്രസന്റേഷൻ സെക്ഷനിൽ നിന്ന് പ്രത്യേക 'ക്വാഫ് ഖലം' (QUAF Qalam) സമ്മാനം കൈപ്പറ്റാം.
                    @endif
                </p>
            </div>

            <!-- Status Indicator & Action Button -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-shrink-0">
                @if($student->recognition_gift_delivered)
                    <div class="px-5 py-3 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-950 flex items-center gap-3 shadow-2xs">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold flex-shrink-0 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold font-sora text-emerald-900">സമ്മാനം കൈപ്പറ്റി (Delivered)</div>
                            <div class="text-[10px] font-mono text-emerald-700">
                                {{ $student->recognition_gift_delivered_at?->format('d M, h:i A') }} • {{ $student->recognition_gift_delivered_by ?? 'Presentation Desk' }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="px-4 py-3 rounded-2xl bg-amber-50 border border-amber-300 text-amber-950 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center font-bold flex-shrink-0 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold font-sora text-amber-900">കൈപ്പറ്റാൻ റെഡി (Pending)</div>
                            <div class="text-[10px] font-mono text-amber-700">Presentation Counter</div>
                        </div>
                    </div>

                    <button type="button" 
                            @click="showQrModal = true"
                            class="px-5 py-3 rounded-2xl bg-[#be1e2d] hover:bg-[#a01824] text-white font-sora font-bold text-xs uppercase tracking-wider transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                        <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        <span>Show QR Code to Collect</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- QR Code Popup Modal for Quick Desk Scanning -->
        <div x-show="showQrModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
             @keydown.escape.window="showQrModal = false">
            <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-sm w-full space-y-4 text-center shadow-2xl border border-slate-200"
                 @click.away="showQrModal = false">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-500">Official Fest QR</span>
                    <button type="button" @click="showQrModal = false" class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm cursor-pointer">&times;</button>
                </div>

                <div class="space-y-1">
                    <h3 class="font-sora font-black text-xl text-slate-900">{{ $giftTitle }}</h3>
                    <p class="text-xs font-anek text-slate-500">പ്രസന്റേഷൻ ഡെസ്കിൽ ഈ ക്യുആർ കോഡ് കാണിച്ച് സമ്മാനം കൈപ്പറ്റുക</p>
                </div>

                <div class="p-4 bg-slate-50 rounded-2xl border-2 border-slate-200 inline-block shadow-inner">
                    <img src="{{ $qrImageUrl }}" alt="QR Code" class="w-56 h-56 mx-auto rounded-lg shadow-xs">
                </div>

                <div class="space-y-0.5 font-mono text-xs text-slate-700">
                    <div class="font-bold text-slate-900 text-sm">{{ $student->name }}</div>
                    <div>Chest / ID: <strong class="text-[#be1e2d]">#{{ $chest }}</strong></div>
                    <div class="text-[11px] text-slate-500">{{ $student->group->name }} &bull; {{ $student->zone_name }}</div>
                </div>

                <button type="button" @click="showQrModal = false" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-xs uppercase cursor-pointer transition">
                    Done / Close
                </button>
            </div>
        </div>
    </div>

    <!-- Official Competition Results & Achievements (1st/2nd/3rd Prizes & Grades) -->
    @if(isset($awardedPrograms) && $awardedPrograms->isNotEmpty())
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-7 shadow-sm space-y-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#be1e2d]"></span>
                    <h3 class="text-sm font-mono font-bold uppercase tracking-wider text-slate-900">
                        Official Competition Results &amp; Achievements
                    </h3>
                </div>
                <span class="text-xs font-mono text-slate-500 font-bold px-3 py-1 bg-slate-100 rounded-full border border-slate-200">
                    {{ $awardedPrograms->count() }} {{ \Illuminate\Support\Str::plural('Award', $awardedPrograms->count()) }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                @foreach($awardedPrograms as $result)
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/90 hover:border-red-300 transition-all flex items-center justify-between gap-3 shadow-2xs">
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

    <!-- Official Individual Programme Quota Meter (5 Max) -->
    <div class="rounded-3xl bg-white border border-slate-200/90 p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6 hover:shadow-md transition-shadow">
        <div class="space-y-2 w-full sm:w-auto">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ ($individualCount ?? 0) >= 5 ? 'bg-[#be1e2d]' : 'bg-emerald-500 animate-pulse' }}"></span>
                <h3 class="text-sm font-mono font-bold uppercase tracking-wider text-slate-900">Individual Programme Quota</h3>
            </div>
            <p class="text-xs text-slate-500 font-sora">
                Each student can participate in a maximum of 5 individual programmes (Own Zone + Mix Zone).
            </p>
            <!-- 5 Visual Pill Indicators -->
            <div class="flex items-center gap-1.5 pt-1 max-w-xs">
                @for($i = 1; $i <= ($maxSlots ?? 5); $i++)
                    <div class="h-1.5 flex-1 rounded-full {{ $i <= ($individualCount ?? 0) ? 'bg-[#be1e2d]' : 'bg-slate-200' }}"></div>
                @endfor
            </div>
        </div>
        <div class="flex items-center gap-5 flex-shrink-0 bg-slate-50 border border-slate-200/80 px-6 py-3.5 rounded-2xl">
            <div class="text-right">
                <div class="text-2xl font-sora font-black">
                    <span class="{{ ($individualCount ?? 0) >= 5 ? 'text-[#be1e2d]' : 'text-emerald-700' }}">{{ $individualCount ?? 0 }}</span>
                    <span class="text-slate-400 text-lg">/ {{ $maxSlots ?? 5 }}</span>
                </div>
                <span class="text-[10px] font-mono font-bold uppercase text-slate-500 tracking-wider">Used Slots</span>
            </div>
            <div class="h-8 w-[1px] bg-slate-300"></div>
            <div class="text-left">
                <div class="text-2xl font-sora font-black {{ ($remainingSlots ?? 0) > 0 ? 'text-[#be1e2d]' : 'text-slate-400' }}">
                    {{ $remainingSlots ?? 5 }}
                </div>
                <span class="text-[10px] font-mono font-bold uppercase text-slate-500 tracking-wider">Remaining</span>
            </div>
        </div>
    </div>

    <!-- PROMINENT NEXT PROGRAM COUNTDOWN WIDGET (Clean Standard Design) -->
    <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 relative overflow-hidden shadow-sm hover:shadow-md transition-shadow">
        <!-- Subtle Ambient Background Glow -->
        <div class="absolute -top-16 -right-16 w-52 h-52 bg-gradient-to-br from-[#be1e2d]/8 to-transparent rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <div class="space-y-2.5">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 border border-red-200 text-[#be1e2d] text-xs font-mono font-bold shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-[#be1e2d] shadow-[0_0_6px_#be1e2d] animate-ping"></span>
                    <span>NEXT PROGRAM STAGE SLOT</span>
                </div>

                @if($nextEntry)
                    <h2 class="text-2xl sm:text-3xl font-sora font-black text-slate-900 tracking-tight">
                        {{ $nextEntry->program->name }}
                    </h2>
                    <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs font-mono text-slate-600">
                        <span>Venue: <strong class="text-slate-900 font-bold">{{ $nextEntry->program->stage->name ?? 'TBA' }}</strong></span>
                        <span class="text-slate-300">•</span>
                        <span>Scheduled: <strong class="text-[#be1e2d] font-bold">{{ $nextEntry->program->scheduled_time?->format('M d, h:i A') }}</strong></span>
                    </div>
                @else
                    <h2 class="text-xl font-sora font-bold text-slate-700">
                        No upcoming programs scheduled at this time.
                    </h2>
                    <p class="text-xs font-mono text-slate-500">
                        Check back once the next stage schedules are announced by the festival desk.
                    </p>
                @endif
            </div>

            @if($nextEntry && $nextEntry->program->scheduled_time)
                <!-- Live Countdown Timer (Theme Red Digit Cards) -->
                <div x-data="{
                    target: new Date('{{ $nextEntry->program->scheduled_time->toIso8601String() }}').getTime(),
                    now: new Date().getTime(),
                    days: 0, hours: 0, minutes: 0, seconds: 0,
                    init() {
                        this.update();
                        setInterval(() => { this.now = new Date().getTime(); this.update(); }, 1000);
                    },
                    update() {
                        let diff = this.target - this.now;
                        if (diff <= 0) {
                            this.days = 0; this.hours = 0; this.minutes = 0; this.seconds = 0;
                            return;
                        }
                        this.days = Math.floor(diff / (1000 * 60 * 60 * 24));
                        this.hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        this.minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                        this.seconds = Math.floor((diff % (1000 * 60)) / 1000);
                    }
                }" class="flex items-center gap-2 sm:gap-3 font-mono">
                    <div class="bg-white border border-slate-200/80 hover:border-red-200 rounded-2xl p-3 sm:p-3.5 text-center min-w-[68px] sm:min-w-[76px] shadow-sm transition-all">
                        <span class="text-2xl sm:text-3xl font-black text-[#be1e2d] tracking-tight block" x-text="days">0</span>
                        <span class="text-[9px] text-slate-500 block uppercase mt-0.5 font-bold tracking-wider">Days</span>
                    </div>
                    <div class="bg-white border border-slate-200/80 hover:border-red-200 rounded-2xl p-3 sm:p-3.5 text-center min-w-[68px] sm:min-w-[76px] shadow-sm transition-all">
                        <span class="text-2xl sm:text-3xl font-black text-[#be1e2d] tracking-tight block" x-text="hours">0</span>
                        <span class="text-[9px] text-slate-500 block uppercase mt-0.5 font-bold tracking-wider">Hours</span>
                    </div>
                    <div class="bg-white border border-slate-200/80 hover:border-red-200 rounded-2xl p-3 sm:p-3.5 text-center min-w-[68px] sm:min-w-[76px] shadow-sm transition-all">
                        <span class="text-2xl sm:text-3xl font-black text-[#be1e2d] tracking-tight block" x-text="minutes">0</span>
                        <span class="text-[9px] text-slate-500 block uppercase mt-0.5 font-bold tracking-wider">Mins</span>
                    </div>
                    <div class="bg-white border border-slate-200/80 hover:border-red-200 rounded-2xl p-3 sm:p-3.5 text-center min-w-[68px] sm:min-w-[76px] shadow-sm transition-all">
                        <span class="text-2xl sm:text-3xl font-black text-[#be1e2d] tracking-tight block" x-text="seconds">0</span>
                        <span class="text-[9px] text-slate-500 block uppercase mt-0.5 font-bold tracking-wider">Secs</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Enrolled Programs Grid (Standard Card Design) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-sora font-bold text-slate-900">My Enrolled Programs</h2>
            <span class="text-xs font-mono text-slate-500 font-semibold px-3 py-1 bg-slate-100 rounded-full border border-slate-200">Total: {{ $myPrograms->count() }} Events</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($myPrograms as $entry)
                <div class="bg-white border border-slate-200/90 rounded-3xl p-6 space-y-4 hover:border-red-300 transition-all shadow-sm hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase">
                            {{ $entry->program->zone?->name ?? $entry->program->eligibility ?? 'All Zones' }} • {{ ucfirst($entry->program->type) }}
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-mono uppercase font-bold
                            {{ $entry->status === 'verified' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                            {{ $entry->status }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-xl font-sora font-bold text-slate-900">{{ $entry->program->name }}</h3>
                    </div>

                    <div class="pt-4 border-t border-slate-100 grid grid-cols-2 gap-3 text-xs font-mono">
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Stage Venue</span>
                            <span class="text-slate-900 font-bold">{{ $entry->program->stage->name ?? 'TBA' }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Schedule Slot</span>
                            <span class="text-[#be1e2d] font-bold">{{ $entry->program->scheduled_time?->format('M d, h:i A') ?? 'TBA' }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-slate-500 font-mono text-xs bg-white rounded-3xl border border-slate-200 shadow-sm">
                    You have not been enrolled in any programs yet. Contact your Group Leader.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Announcements -->
    @if($announcements->isNotEmpty())
        <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-7 space-y-4 shadow-sm">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#be1e2d]"></span>
                <h3 class="text-lg font-sora font-bold text-slate-900">Festival Announcements</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($announcements as $ann)
                    <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-1 hover:border-slate-300 transition">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-sora font-bold text-slate-900">{{ $ann->title }}</h4>
                            <span class="text-[10px] font-mono text-slate-400">{{ $ann->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs font-sora text-slate-700 leading-relaxed">{{ $ann->content }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
