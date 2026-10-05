@extends('layouts.public', ['title' => $program->name . ' — Results | QUAF 9.0'])

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16">
    
    <!-- Breadcrumb & Back -->
    <div class="mb-6">
        <a href="{{ route('results.index') }}" class="text-xs font-mono text-[#be1e2d] hover:underline flex items-center gap-1.5 mb-3 font-semibold">
            <span>← Back to All Results</span>
        </a>
        <div class="flex flex-wrap items-center gap-2 mb-2">
            <span class="px-2.5 py-1 rounded bg-red-50 border border-red-200 text-[#be1e2d] font-mono text-xs font-bold">{{ $program->code }}</span>
            <span class="text-slate-500 text-xs font-mono">{{ $program->eligibility ?? 'All Zones' }}</span>
            <span class="text-slate-300">•</span>
            <span class="text-slate-500 text-xs font-mono">{{ $program->category?->name ?? 'Cultural Arts' }}</span>
            <span class="text-slate-300">•</span>
            <span class="text-slate-500 text-xs font-mono">Stage: {{ $program->stage?->name ?? 'Designated Arena' }}</span>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 leading-tight">
                    {{ $program->name }}
                </h1>
                @if($program->malayalam_name)
                    <p class="text-sm sm:text-base text-slate-500 font-malayalam mt-1">{{ $program->malayalam_name }}</p>
                @endif
            </div>

            @if($result)
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('media.results.public-poster', $result->id) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#991522] text-white font-bold text-xs shadow-xs transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>Result Poster</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    @if($result)
        <!-- Podium Winners Banner (Section 17) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-8 sm:mb-12 font-sora">
            <!-- 1st Place (Center / Highlight) -->
            <div class="md:order-2 rounded-3xl bg-gradient-to-b from-amber-50 to-white border-2 border-amber-300 p-6 sm:p-8 text-center relative overflow-hidden shadow-md">
                <div class="w-12 h-12 rounded-full bg-[#f3bd2e] text-white font-sora font-black text-xl flex items-center justify-center mx-auto mb-3 shadow-xs">1</div>
                <span class="text-xs font-mono font-bold tracking-widest text-amber-700 uppercase block mb-1">FIRST PLACE / WINNER</span>
                <h3 class="text-xl sm:text-2xl font-sora font-black text-slate-900 mb-2 leading-snug">
                    {{ $result->firstEntry?->student?->name ?? 'Team ' . $result->firstEntry?->group?->name }}
                </h3>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-amber-200 text-xs font-mono text-slate-700 mb-3 shadow-2xs">
                    <span>Chest #{{ $result->firstEntry?->chest_number }}</span>
                    <span>•</span>
                    <span class="font-bold text-[#be1e2d]">{{ $result->firstEntry?->group?->name }}</span>
                </div>
                @if($result->firstEntry?->certificate)
                    <div class="pt-2">
                        <a href="{{ route('verify.certificate', $result->firstEntry->certificate->certificate_number) }}" class="text-xs font-mono text-amber-700 hover:underline font-semibold">
                            Verify Certificate #{{ $result->firstEntry->certificate->certificate_number }} →
                        </a>
                    </div>
                @endif
            </div>

            <!-- 2nd Place -->
            @if($result->secondEntry)
                <div class="md:order-1 rounded-3xl bg-white border border-slate-200 p-5 sm:p-6 text-center shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-full bg-slate-300 text-slate-800 font-sora font-black text-lg flex items-center justify-center mx-auto mb-3">2</div>
                        <span class="text-xs font-mono font-bold tracking-widest text-slate-500 uppercase block mb-1">SECOND PLACE</span>
                        <h3 class="text-lg sm:text-xl font-sora font-bold text-slate-900 mb-2 leading-snug">
                            {{ $result->secondEntry?->student?->name ?? 'Team ' . $result->secondEntry?->group?->name }}
                        </h3>
                    </div>
                    <div class="inline-flex items-center justify-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-xs font-mono text-slate-700 mt-2">
                        <span>Chest #{{ $result->secondEntry?->chest_number }}</span>
                        <span>•</span>
                        <span class="font-bold text-slate-800">{{ $result->secondEntry?->group?->name }}</span>
                    </div>
                </div>
            @endif

            <!-- 3rd Place -->
            @if($result->thirdEntry)
                <div class="md:order-3 rounded-3xl bg-white border border-slate-200 p-5 sm:p-6 text-center shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-full bg-amber-700 text-white font-sora font-black text-lg flex items-center justify-center mx-auto mb-3">3</div>
                        <span class="text-xs font-mono font-bold tracking-widest text-slate-500 uppercase block mb-1">THIRD PLACE</span>
                        <h3 class="text-lg sm:text-xl font-sora font-bold text-slate-900 mb-2 leading-snug">
                            {{ $result->thirdEntry?->student?->name ?? 'Team ' . $result->thirdEntry?->group?->name }}
                        </h3>
                    </div>
                    <div class="inline-flex items-center justify-center gap-2 px-3 py-1 rounded-full bg-amber-50 text-xs font-mono text-slate-700 mt-2">
                        <span>Chest #{{ $result->thirdEntry?->chest_number }}</span>
                        <span>•</span>
                        <span class="font-bold text-amber-900">{{ $result->thirdEntry?->group?->name }}</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Full Candidate Score Sheet Table (Section 17) -->
        <div class="rounded-3xl bg-white border border-slate-200 overflow-hidden shadow-xs mb-8">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <h3 class="font-sora font-bold text-base text-slate-900">Official Candidate Score Sheet</h3>
                <span class="text-xs font-mono text-slate-500">{{ $program->entries->count() }} Contestants</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[500px]">
                    <thead class="bg-slate-100 text-slate-600 font-mono text-xs uppercase">
                        <tr>
                            <th class="px-6 py-3">Chest #</th>
                            <th class="px-6 py-3">Candidate / Delegate</th>
                            <th class="px-6 py-3">Group</th>
                            <th class="px-6 py-3 text-center">Grade</th>
                            <th class="px-6 py-3 text-right">Award / Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @foreach($program->entries as $entry)
                            @php
                                $isFirst = $result->first_entry_id === $entry->id;
                                $isSecond = $result->second_entry_id === $entry->id;
                                $isThird = $result->third_entry_id === $entry->id;
                            @endphp
                            <tr class="{{ $isFirst ? 'bg-amber-50/60 font-semibold' : '' }}">
                                <td class="px-6 py-3.5 font-mono text-xs">{{ $entry->chest_number }}</td>
                                <td class="px-6 py-3.5">
                                    <div class="font-sora text-slate-900">{{ $entry->student?->name ?? 'Team ' . $entry->group?->name }}</div>
                                    @if($entry->student?->category)
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $entry->student->category }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-1.5 font-mono text-xs">
                                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $entry->group?->color_hex ?? '#be1e2d' }}"></span>
                                        <span>{{ $entry->group?->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3.5 text-center font-mono">
                                    @if($entry->grade)
                                        <span class="px-2 py-0.5 rounded text-xs font-bold {{ $entry->grade === 'A+' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                                            {{ $entry->grade }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 text-right font-mono">
                                    @if($isFirst)
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                            1st Place (5 pts)
                                        </span>
                                    @elseif($isSecond)
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                            2nd Place (3 pts)
                                        </span>
                                    @elseif($isThird)
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            3rd Place (1 pt)
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">Evaluated</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Social Share & Print Bar (Section 36) -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4 font-mono text-xs">
            <div class="text-slate-600">
                Official verdict declared on <strong class="text-slate-900">{{ $result->published_at?->format('M d, Y • h:i A') ?? 'Verified' }}</strong>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-slate-400">Share:</span>
                <a href="https://api.whatsapp.com/send?text={{ urlencode('QUAF 9.0 Result: ' . $program->code . ' ' . $program->name . ' - ' . url()->current()) }}"
                   target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold hover:bg-emerald-100 transition-colors">
                    WhatsApp
                </a>
                <a href="https://twitter.com/intent/tweet?text={{ urlencode('QUAF 9.0 Result: ' . $program->code . ' ' . $program->name) }}&url={{ urlencode(url()->current()) }}"
                   target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                    X / Twitter
                </a>
                <button onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!');"
                        class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                    Copy Link
                </button>
            </div>
        </div>

    @else
        <!-- Result Not Declared Yet State (Section 38) -->
        <div class="p-8 sm:p-12 rounded-3xl bg-white border border-slate-200 text-center shadow-xs max-w-2xl mx-auto my-8 space-y-4">
            <div class="w-14 h-14 rounded-full bg-blue-50 text-[#005c94] flex items-center justify-center mx-auto mb-2">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h2 class="text-xl sm:text-2xl font-sora font-black text-slate-900">
                Verdicts Awaiting Declaration
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed max-w-lg mx-auto">
                Official results for <strong>{{ $program->code }} — {{ $program->name }}</strong> have not been published yet. Jury evaluation or verification is currently in progress.
            </p>
            <div class="pt-2 flex justify-center gap-3 font-mono text-xs">
                <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700">Status: {{ ucfirst($program->status) }}</span>
                <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700">{{ $program->entries->count() }} Registered Candidates</span>
            </div>
        </div>
    @endif

</div>
@endsection
