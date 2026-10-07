@extends('layouts.public', ['title' => $group->name . ' — QUAF Academic House'])

@section('content')

<!-- Header Section with Group Color Accent -->
<section class="py-12 sm:py-16 bg-white border-b border-slate-200 relative overflow-hidden"
         style="border-top: 6px solid {{ $group->color_hex }};">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <span class="w-4 h-4 rounded-full shadow-2xs" style="background-color: {{ $group->color_hex }}"></span>
                    <span class="font-mono text-xs font-bold uppercase tracking-wider text-slate-600">{{ $group->code }} • ACADEMIC HOUSE</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-100 text-slate-800 border border-slate-200">
                        Rank #{{ $group->rank_cache ?? '—' }}
                    </span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-sora font-black text-slate-900 tracking-tight">
                    {{ $group->name }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-mono">
                    House Captain / Manager: <strong class="text-slate-800">{{ $group->manager_name ?: ($group->leader?->name ?? 'House Leadership') }}</strong>
                </p>
            </div>

            <!-- Tally Card -->
            <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200 text-center md:text-right font-mono shrink-0">
                <span class="text-xs text-slate-400 uppercase font-semibold block mb-1">Official Points Tally</span>
                <div class="text-4xl sm:text-5xl font-rockwell font-bold" style="color: {{ $group->color_hex }}">
                    {{ number_format($group->points_cache) }}
                </div>
                <span class="text-[11px] text-slate-500 mt-1 block">Aggregated Score</span>
            </div>

        </div>
    </div>
</section>

<!-- Content Section: Wins & Delegates -->
<section class="py-12 sm:py-16 bg-slate-50 min-h-[60vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Col 1 & 2: Official Wins / Achievements in Festival -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-mono uppercase tracking-widest text-[#be1e2d] font-bold block">VERDICTS & VICTORIES</span>
                        <h2 class="text-xl sm:text-2xl font-sora font-black text-slate-900">Declared Program Victories</h2>
                    </div>
                    <span class="text-xs font-mono text-slate-500 font-bold bg-white px-3 py-1 rounded-xl border border-slate-200">
                        {{ $groupWins->count() }} Podium Finishes
                    </span>
                </div>

                @forelse($groupWins as $win)
                    @php
                        $is1st = $win->firstEntry?->group_id === $group->id;
                        $is2nd = $win->secondEntry?->group_id === $group->id;
                        $is3rd = $win->thirdEntry?->group_id === $group->id;
                    @endphp
                    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-2xs hover:shadow-md transition-shadow flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            @if($is1st)
                                <span class="w-10 h-10 rounded-xl bg-amber-100 text-amber-900 font-sora font-black text-sm flex items-center justify-center shrink-0 border border-amber-300">
                                    1st
                                </span>
                            @elseif($is2nd)
                                <span class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 font-sora font-black text-sm flex items-center justify-center shrink-0 border border-slate-300">
                                    2nd
                                </span>
                            @else
                                <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-800 font-sora font-black text-sm flex items-center justify-center shrink-0 border border-amber-200">
                                    3rd
                                </span>
                            @endif

                            <div>
                                <span class="font-mono text-xs text-[#be1e2d] font-bold block">{{ $win->program->code }} • {{ $win->program->eligibility }}</span>
                                <h3 class="text-base font-sora font-bold text-slate-900">{{ $win->program->name }}</h3>
                                <p class="text-xs text-slate-500 font-mono mt-0.5">
                                    Candidate: <strong>{{ ($is1st ? $win->firstEntry?->student?->name : ($is2nd ? $win->secondEntry?->student?->name : $win->thirdEntry?->student?->name)) ?? 'Team Event' }}</strong>
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('results.show', $win->program->id) }}" class="text-xs font-bold text-[#be1e2d] hover:underline whitespace-nowrap">
                            Score Sheet →
                        </a>
                    </div>
                @empty
                    <div class="p-8 bg-white rounded-2xl border border-slate-200 text-center text-slate-500 font-mono text-xs">
                        Official verdicts involving this group have not been published yet. Check back as results conclude.
                    </div>
                @endforelse
            </div>

            <!-- Col 3: House Roster & Delegates -->
            <div class="space-y-6">
                <div>
                    <span class="text-xs font-mono uppercase tracking-widest text-[#be1e2d] font-bold block">HOUSE CONTINGENT</span>
                    <h2 class="text-xl sm:text-2xl font-sora font-black text-slate-900">Top Scoring Delegates</h2>
                </div>

                <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-2xs divide-y divide-slate-100 font-mono text-xs">
                    @forelse($students as $st)
                        <div class="py-3 flex items-center justify-between first:pt-0 last:pb-0">
                            <div>
                                <div class="font-bold text-slate-900 font-sora">{{ $st->name }}</div>
                                <span class="text-[10px] text-slate-400">Chest #{{ $st->chest_number }} • {{ $st->category }}</span>
                            </div>
                            <span class="font-bold text-slate-800 text-sm">{{ $st->points_cache }} pts</span>
                        </div>
                    @empty
                        <div class="py-4 text-center text-slate-400">
                            No delegates currently listed.
                        </div>
                    @endforelse
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs space-y-3">
                    <h3 class="font-sora font-bold text-slate-900 text-sm">House Verification</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        To verify a student delegate's festival accreditation card or certificate, use the unified verification portal.
                    </p>
                    <a href="{{ route('verify.index') }}" class="block w-full text-center py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold uppercase tracking-wider transition-colors">
                        QR Verification Portal
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>

@endsection
