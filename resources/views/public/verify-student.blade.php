@extends('layouts.public', ['title' => 'Participant Verification — QUAF'])

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-8">
    
    <div class="text-center">
        <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">PARTICIPANT IDENTIFICATION & WALLET</span>
        <h1 class="text-3xl sm:text-4xl font-sora font-black text-slate-900 mt-1">Official Delegate Profile</h1>
        <p class="text-xs text-slate-500 font-anek mt-1">ഐഡി കാർഡ് സ്കാൻ ചെയ്തുകൊണ്ടുള്ള ഔദ്യോഗിക വിവരങ്ങളും കോയിൻ ബാലൻസും</p>
    </div>

    @if($isValid && $student)
        <!-- Valid Student Profile -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-10 shadow-lg space-y-8">
            
            <!-- Student Header Profile -->
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 pb-6 border-b border-slate-200">
                <div class="w-28 h-28 rounded-2xl overflow-hidden bg-slate-100 border-2 border-slate-200 shadow-md shrink-0 flex items-center justify-center">
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

                <div class="flex-1 text-center sm:text-left min-w-0">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-2">
                        <span class="px-3 py-1 rounded-lg text-xs font-mono font-bold bg-red-50 text-[#be1e2d] border border-red-200">
                            Chest #: {{ $student->chest_number ?? $student->student_id }}
                        </span>
                        <span class="px-3 py-1 rounded-lg text-xs font-mono font-bold" style="background-color: {{ $student->group?->color_hex ?? '#2e3192' }}20; color: {{ $student->group?->color_hex ?? '#2e3192' }}">
                            House: {{ $student->group?->name ?? 'QUAF' }}
                        </span>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-mono bg-slate-100 text-slate-700">
                            {{ $student->zone_name }}
                        </span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-sora font-black text-slate-900 truncate">{{ $student->name }}</h2>
                    <div class="text-xs font-mono text-slate-500 space-y-0.5 mt-1">
                        <p>Class / Category: <strong class="text-slate-700">{{ $student->class_level ?? $student->category }}</strong></p>
                        <p>Festival Contribution: <strong class="text-[#be1e2d] font-bold">{{ $student->points_cache }} Points</strong></p>
                    </div>
                </div>

                <!-- Open Full Portal Button -->
                <div class="shrink-0 flex flex-col gap-2 w-full sm:w-auto">
                    <a href="{{ route('student.qr-login', $student->qr_token) }}" 
                       class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs font-mono uppercase tracking-wider text-center transition-all shadow-md">
                        Open Student Portal
                    </a>
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'presentation_team'))
                        <a href="{{ route('presentation.dashboard', ['student' => $student->student_id]) }}" 
                           class="px-4 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01824] text-white font-bold text-xs font-mono uppercase tracking-wider text-center transition-all shadow-md">
                            Open in Presentation Desk
                        </a>
                    @endif
                </div>
            </div>

            <!-- SECRET PURCHASE PIN CARD (Visible only on physical QR scan) -->
            <div class="p-6 sm:p-7 rounded-3xl bg-slate-950 text-white border-2 border-amber-400/50 shadow-xl relative overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-xs font-mono font-bold uppercase tracking-widest text-amber-400">Secret Purchase Code (സീക്രട്ട് പർച്ചേസ് കോഡ്)</span>
                        </div>
                        <p class="text-xs text-slate-300 font-anek mt-1">
                            പ്രസന്റേഷൻ കൗണ്ടറിൽ നിന്നും സമ്മാനങ്ങളും പുസ്തകങ്ങളും വാങ്ങുമ്പോൾ ഈ 4 അക്ക രഹസ്യ കോഡ് നൽകുക.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 self-start sm:self-center">
                        <span class="sr-only">{{ $student->purchase_pin }}</span>
                        @foreach(str_split((string) $student->purchase_pin) as $digit)
                            <div class="w-12 h-14 rounded-2xl bg-slate-900 border-2 border-amber-400 text-amber-300 font-mono font-black text-2xl flex items-center justify-center shadow-lg">
                                {{ $digit }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-800 text-[11px] font-mono text-slate-400 flex items-center justify-between">
                    <span>Physical QR Badge Authentication: Verified</span>
                    <span class="text-amber-300/80">Keep this PIN confidential</span>
                </div>
            </div>

            <!-- COIN WALLET BALANCE GRID -->
            @if($coinSummary)
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Available Balance -->
                    <div class="p-5 rounded-2xl bg-gradient-to-br from-amber-500 via-amber-400 to-yellow-500 text-slate-950 shadow-md border border-amber-300">
                        <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-800">Available Wallet Balance</div>
                        <div class="text-3xl sm:text-4xl font-black mt-1 font-sora flex items-baseline gap-2">
                            <span>{{ number_format($coinSummary['balance']) }}</span>
                            <span class="text-xs font-mono uppercase font-bold text-slate-800">Coins</span>
                        </div>
                        <div class="text-[11px] font-anek text-slate-900 mt-2 font-medium">കൈവശമുള്ള ആകെ കോയിനുകൾ</div>
                    </div>

                    <!-- Total Earned -->
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400">Total Coins Earned</div>
                        <div class="text-2xl sm:text-3xl font-black mt-1 font-sora text-emerald-600">
                            +{{ number_format($coinSummary['total_earned']) }}
                        </div>
                        <div class="text-[11px] font-anek text-slate-500 mt-2 font-medium">വിവിധ മത്സരങ്ങളിൽ നിന്ന് ലഭിച്ചത്</div>
                    </div>

                    <!-- Total Spent -->
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-400">Total Coins Spent</div>
                        <div class="text-2xl sm:text-3xl font-black mt-1 font-sora text-rose-600">
                            -{{ number_format($coinSummary['total_spent']) }}
                        </div>
                        <div class="text-[11px] font-anek text-slate-500 mt-2 font-medium">സമ്മാനങ്ങൾക്കായി ചെലവഴിച്ചത്</div>
                    </div>
                </div>
            @endif

            <!-- GRADE ACHIEVEMENTS & COMPETITION PRIZES -->
            @if(isset($studentAchievements))
                <div class="space-y-6 pt-2">
                    <!-- Grade Breakdown Row (A+ Excellence, A Proficient, B Adequate, C Satisfactory) -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-400">Grade Achievements</span>
                            <span class="text-xs font-mono text-slate-500">Official Evaluation</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <!-- A+ Excellence -->
                            <div class="rounded-2xl p-4 bg-gradient-to-b from-slate-900 to-slate-950 text-white border border-slate-800 text-center shadow-sm">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="text-2xl font-black font-sora text-amber-400">A+</span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">
                                        {{ $studentAchievements['grade_counts']['A+'] ?? 0 }}
                                    </span>
                                </div>
                                <span class="text-xs text-slate-400 font-medium block mt-1">Excellence</span>
                            </div>

                            <!-- A Proficient -->
                            <div class="rounded-2xl p-4 bg-gradient-to-b from-slate-900 to-slate-950 text-white border border-slate-800 text-center shadow-sm">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="text-2xl font-black font-sora text-emerald-400">A</span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-400/20 text-emerald-300 border border-emerald-400/30">
                                        {{ $studentAchievements['grade_counts']['A'] ?? 0 }}
                                    </span>
                                </div>
                                <span class="text-xs text-slate-400 font-medium block mt-1">Proficient</span>
                            </div>

                            <!-- B Adequate -->
                            <div class="rounded-2xl p-4 bg-gradient-to-b from-slate-900 to-slate-950 text-white border border-slate-800 text-center shadow-sm">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="text-2xl font-black font-sora text-blue-400">B</span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-blue-400/20 text-blue-300 border border-blue-400/30">
                                        {{ $studentAchievements['grade_counts']['B'] ?? 0 }}
                                    </span>
                                </div>
                                <span class="text-xs text-slate-400 font-medium block mt-1">Adequate</span>
                            </div>

                            <!-- C Satisfactory -->
                            <div class="rounded-2xl p-4 bg-gradient-to-b from-slate-900 to-slate-950 text-white border border-slate-800 text-center shadow-sm">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="text-2xl font-black font-sora text-slate-300">C</span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-700/50 text-slate-300 border border-slate-600/30">
                                        {{ $studentAchievements['grade_counts']['C'] ?? 0 }}
                                    </span>
                                </div>
                                <span class="text-xs text-slate-400 font-medium block mt-1">Satisfactory</span>
                            </div>
                        </div>
                    </div>

                    <!-- Competition Prizes (First Prize Pinnacle, Second Prize Eminent, Third Prize Competent) -->
                    <div class="space-y-3">
                        <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-400 block">Competition Prizes</span>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- First Prize -->
                            @php $p1 = $studentAchievements['prizes'][1] ?? ['count' => 0, 'programs' => []]; @endphp
                            <div class="rounded-2xl p-4 border transition-all {{ $p1['count'] > 0 ? 'bg-[#0f1d36] text-white border-amber-400/60 shadow-md' : 'bg-slate-50 text-slate-700 border-slate-200' }}" x-data="{ open: false }">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-mono font-bold {{ $p1['count'] > 0 ? 'bg-amber-400 text-slate-950' : 'bg-slate-200 text-slate-600' }}">
                                        <svg class="w-3.5 h-3.5 text-current shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        <span>{{ $p1['count'] }} Pinnacle</span>
                                    </span>
                                    @if(!empty($p1['programs']))
                                        <button type="button" @click="open = !open" class="text-xs font-mono font-bold text-amber-300 hover:text-amber-200 underline cursor-pointer">
                                            <span x-text="open ? 'Hide Details' : 'View Details'"></span>
                                        </button>
                                    @endif
                                </div>
                                <h4 class="text-base font-black font-sora mt-2 {{ $p1['count'] > 0 ? 'text-white' : 'text-slate-800' }}">First Prize</h4>
                                <p class="text-xs mt-0.5 {{ $p1['count'] > 0 ? 'text-slate-300' : 'text-slate-500' }}">You've secured the First Prize with excellence!</p>

                                @if(!empty($p1['programs']))
                                    <div x-show="open" x-cloak class="mt-3 pt-3 border-t border-white/10 space-y-1.5">
                                        @foreach($p1['programs'] as $prog)
                                            <div class="flex items-center justify-between text-xs bg-white/5 rounded-lg px-2.5 py-1.5">
                                                <span class="font-medium truncate">{{ $prog['name'] }}</span>
                                                <span class="text-[11px] font-mono font-bold text-amber-300 shrink-0 ml-2">{{ $prog['grade'] ? 'Grade '.$prog['grade'] : '' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Second Prize -->
                            @php $p2 = $studentAchievements['prizes'][2] ?? ['count' => 0, 'programs' => []]; @endphp
                            <div class="rounded-2xl p-4 border transition-all {{ $p2['count'] > 0 ? 'bg-[#0f1d36] text-white border-blue-400/60 shadow-md' : 'bg-slate-50 text-slate-700 border-slate-200' }}" x-data="{ open: false }">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-mono font-bold {{ $p2['count'] > 0 ? 'bg-blue-400 text-slate-950' : 'bg-slate-200 text-slate-600' }}">
                                        <svg class="w-3.5 h-3.5 text-current shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        <span>{{ $p2['count'] }} Eminent</span>
                                    </span>
                                    @if(!empty($p2['programs']))
                                        <button type="button" @click="open = !open" class="text-xs font-mono font-bold text-blue-300 hover:text-blue-200 underline cursor-pointer">
                                            <span x-text="open ? 'Hide Details' : 'View Details'"></span>
                                        </button>
                                    @endif
                                </div>
                                <h4 class="text-base font-black font-sora mt-2 {{ $p2['count'] > 0 ? 'text-white' : 'text-slate-800' }}">Second Prize</h4>
                                <p class="text-xs mt-0.5 {{ $p2['count'] > 0 ? 'text-slate-300' : 'text-slate-500' }}">Great achievement securing Second Prize!</p>

                                @if(!empty($p2['programs']))
                                    <div x-show="open" x-cloak class="mt-3 pt-3 border-t border-white/10 space-y-1.5">
                                        @foreach($p2['programs'] as $prog)
                                            <div class="flex items-center justify-between text-xs bg-white/5 rounded-lg px-2.5 py-1.5">
                                                <span class="font-medium truncate">{{ $prog['name'] }}</span>
                                                <span class="text-[11px] font-mono font-bold text-blue-300 shrink-0 ml-2">{{ $prog['grade'] ? 'Grade '.$prog['grade'] : '' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Third Prize -->
                            @php $p3 = $studentAchievements['prizes'][3] ?? ['count' => 0, 'programs' => []]; @endphp
                            <div class="rounded-2xl p-4 border transition-all {{ $p3['count'] > 0 ? 'bg-[#0f1d36] text-white border-amber-600/60 shadow-md' : 'bg-slate-50 text-slate-700 border-slate-200' }}" x-data="{ open: false }">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-mono font-bold {{ $p3['count'] > 0 ? 'bg-amber-600 text-white' : 'bg-slate-200 text-slate-600' }}">
                                        <svg class="w-3.5 h-3.5 text-current shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        <span>{{ $p3['count'] }} Competent</span>
                                    </span>
                                    @if(!empty($p3['programs']))
                                        <button type="button" @click="open = !open" class="text-xs font-mono font-bold text-amber-300 hover:text-amber-200 underline cursor-pointer">
                                            <span x-text="open ? 'Hide Details' : 'View Details'"></span>
                                        </button>
                                    @endif
                                </div>
                                <h4 class="text-base font-black font-sora mt-2 {{ $p3['count'] > 0 ? 'text-white' : 'text-slate-800' }}">Third Prize</h4>
                                <p class="text-xs mt-0.5 {{ $p3['count'] > 0 ? 'text-slate-300' : 'text-slate-500' }}">Proud moment! You earned the Third Prize.</p>

                                @if(!empty($p3['programs']))
                                    <div x-show="open" x-cloak class="mt-3 pt-3 border-t border-white/10 space-y-1.5">
                                        @foreach($p3['programs'] as $prog)
                                            <div class="flex items-center justify-between text-xs bg-white/5 rounded-lg px-2.5 py-1.5">
                                                <span class="font-medium truncate">{{ $prog['name'] }}</span>
                                                <span class="text-[11px] font-mono font-bold text-amber-300 shrink-0 ml-2">{{ $prog['grade'] ? 'Grade '.$prog['grade'] : '' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- QUAF RECOGNITION GIFT STATUS -->
            @if(!$student->isCompetitionParticipant())
                <div class="p-5 sm:p-6 rounded-2xl border bg-slate-50 border-slate-200 text-slate-700">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-slate-400"></span>
                        <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-600">
                            QUAF Recognition Gift (ക്വാഫ് റെക്കഗ്നിഷൻ സമ്മാനം)
                        </span>
                    </div>
                    <div class="text-base sm:text-lg font-black font-sora mt-1 text-slate-800">
                        അർഹതയില്ല (Not Eligible)
                    </div>
                    <div class="text-xs font-anek text-slate-500 mt-0.5">
                        മത്സരങ്ങളിൽ (Individual / Group) പങ്കെടുത്ത വിദ്യാർത്ഥികൾക്ക് മാത്രമാണ് ക്വാഫ് റെക്കഗ്നിഷൻ സമ്മാനം നൽകുന്നത്.
                    </div>
                </div>
            @else
                <div class="p-5 sm:p-6 rounded-2xl border transition-all {{ $student->recognition_gift_delivered ? 'bg-emerald-50 border-emerald-300 text-emerald-950' : 'bg-amber-50 border-amber-300 text-amber-950' }}">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full {{ $student->recognition_gift_delivered ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                <span class="text-xs font-mono font-bold uppercase tracking-wider">
                                    QUAF Recognition Gift (ക്വാഫ് റെക്കഗ്നിഷൻ സമ്മാനം)
                                </span>
                            </div>
                            
                            @if($student->recognition_gift_delivered)
                                <div class="text-lg font-black font-sora mt-1 text-emerald-900">
                                    സമ്മാനം കൈപ്പറ്റി (Gift Delivered)
                                </div>
                                <div class="text-xs font-mono text-emerald-800 mt-0.5">
                                    തീയതി: {{ $student->recognition_gift_delivered_at?->format('d M Y, h:i A') }} • വിതരണം: {{ $student->recognition_gift_delivered_by ?? 'Presentation Desk' }}
                                </div>
                            @else
                                <div class="text-lg font-black font-sora mt-1 text-amber-900">
                                    സമ്മാനം കൈപ്പറ്റാനുണ്ട് (Pending Collection)
                                </div>
                                <div class="text-xs font-anek text-amber-800 mt-0.5">
                                    പ്രസന്റേഷൻ കൗണ്ടറിൽ നിന്നും നിങ്ങളുടെ ക്വാഫ് റെക്കഗ്നിഷൻ സമ്മാനം കൈപ്പറ്റാവുന്നതാണ്.
                                </div>
                            @endif
                        </div>

                        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'presentation_team'))
                            <form method="POST" action="{{ route('presentation.toggle-gift', $student->id) }}" class="shrink-0">
                                @csrf
                                <button type="submit" 
                                        class="px-4 py-2.5 rounded-xl font-mono font-bold text-xs uppercase tracking-wider transition-all cursor-pointer shadow-sm {{ $student->recognition_gift_delivered ? 'bg-slate-200 hover:bg-slate-300 text-slate-800' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                                    {{ $student->recognition_gift_delivered ? 'Cancel / Revert Status' : 'Mark Gift Delivered (നൽകി എന്ന് അടയാളപ്പെടുത്തുക)' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            <!-- PURCHASE HISTORY & REDEEMED ITEMS -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-sora font-bold text-lg text-slate-900">Purchase History & Gifts (വാങ്ങിയ സമ്മാനങ്ങൾ)</h3>
                        <p class="text-xs text-slate-500 font-anek">പ്രസന്റേഷൻ കൗണ്ടറിൽ നിന്നും കോയിൻ നൽകി വാങ്ങിയ മുഴുവൻ ഇനങ്ങളുടെയും വിവരങ്ങൾ.</p>
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
                        ഇതുവരെ പ്രസന്റേഷൻ കൗണ്ടറിൽ നിന്നും പർച്ചേസുകൾ ഒന്നും നടത്തിയിട്ടില്ല (No purchases recorded yet).
                    </div>
                @endif
            </div>

            <!-- Registered Programs & Stage Status -->
            <div class="space-y-4 pt-4 border-t border-slate-200">
                <h3 class="font-sora font-bold text-lg text-slate-900">Allocated Program Registrations</h3>
                <div class="space-y-3">
                    @forelse($student->entries as $entry)
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 text-xs font-mono text-[#be1e2d] mb-1 font-semibold">
                                    <span class="font-bold">{{ $entry->program?->code }}</span>
                                    <span>•</span>
                                    <span>Chest #{{ $entry->chest_number }}</span>
                                </div>
                                <h4 class="font-sora font-bold text-slate-900 text-base">{{ $entry->program?->name }}</h4>
                                <span class="text-xs text-slate-500 font-mono">{{ $entry->program?->stage?->name ?? 'Stage Schedule Pending' }}</span>
                            </div>
                            <div class="text-right">
                                @if($entry->status === 'verified')
                                    <span class="px-2.5 py-1 rounded text-xs font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">VERIFIED ENTRY</span>
                                @else
                                    <span class="px-2.5 py-1 rounded text-xs font-mono font-bold bg-amber-50 text-amber-700 border border-amber-200">CONFIRMED</span>
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
