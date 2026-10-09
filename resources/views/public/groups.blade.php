@extends('layouts.public', ['title' => 'Academic Groups & Standings — QUAF'])

@section('content')

<!-- Header Section -->
<section class="py-12 sm:py-16 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase animate-subheading">FESTIVAL HOUSES & TEAMS</span>
                <h1 class="text-3xl sm:text-5xl font-sora font-black text-slate-900 mt-1 animate-heading">Academic Groups</h1>
                <p class="text-sm text-slate-600 mt-2 max-w-xl">
                    Official standings and point tallies of competing collegiate groups in QUAF.
                </p>
            </div>
            
            <div class="flex items-center gap-3 font-mono text-xs text-slate-600">
                <span class="px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 font-bold text-slate-800">5 Official Houses</span>
                <span class="px-3 py-1.5 rounded-xl bg-red-50 border border-red-200 text-[#be1e2d] font-bold">120+ Events</span>
            </div>
        </div>
    </div>
</section>

<!-- Groups Grid -->
<section class="py-12 sm:py-16 bg-slate-50 min-h-[60vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($groups as $group)
                @php
                    $isFirst = $loop->iteration === 1;
                    $isSecond = $loop->iteration === 2;
                    $isThird = $loop->iteration === 3;
                @endphp
                <div class="rounded-3xl bg-white border-2 border-slate-200/90 hover:border-slate-300 p-6 sm:p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg flex flex-col justify-between relative overflow-hidden"
                     style="border-top-color: {{ $group->color_hex }}; border-top-width: 6px;">
                    
                    <!-- Ambient Glow -->
                    <div class="absolute -right-16 -top-16 w-36 h-36 rounded-full opacity-10 blur-2xl pointer-events-none"
                         style="background-color: {{ $group->color_hex }}"></div>

                    <div>
                        <!-- Header with Rank Badge -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-3.5 h-3.5 rounded-full shadow-2xs" style="background-color: {{ $group->color_hex }}"></span>
                                <span class="font-mono text-xs text-slate-700 font-bold uppercase tracking-wider">{{ $group->code }}</span>
                            </div>

                            @if($isFirst)
                                <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs">
                                    #1 FESTIVAL LEADER
                                </span>
                            @elseif($isSecond)
                                <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                    #2 SECOND PLACE
                                </span>
                            @elseif($isThird)
                                <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    #3 THIRD PLACE
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-mono font-semibold bg-slate-100 text-slate-600">
                                    #{{ $group->rank_cache ?: $loop->iteration }}
                                </span>
                            @endif
                        </div>

                        <!-- Group Name -->
                        <h2 class="text-2xl sm:text-3xl font-sora font-black text-slate-900 mb-2">
                            {{ $group->name }}
                        </h2>
                        <p class="text-xs text-slate-500 mb-6 flex items-center gap-1.5">
                            <svg class="w-4 h-4 opacity-50 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span>Leadership: <strong>{{ $group->manager_name ?: ($group->leader?->name ?? 'Group Captain') }}</strong></span>
                        </p>

                        <!-- Key Metrics Grid -->
                        <div class="grid grid-cols-2 gap-3 mb-6 p-3 rounded-2xl bg-slate-50 border border-slate-100 font-mono text-xs">
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-semibold block">Delegates</span>
                                <span class="font-bold text-slate-800 text-sm">{{ $group->students_count }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-semibold block">Registrations</span>
                                <span class="font-bold text-slate-800 text-sm">{{ $group->entries_count }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Points Metric & View Detail Link -->
                    <div class="pt-5 border-t border-slate-100 flex items-center justify-between mt-auto">
                        <div>
                            <span class="text-[10px] font-mono uppercase text-slate-400 font-semibold block">Aggregated Points</span>
                            <div class="text-3xl font-rockwell font-bold tracking-tight" style="color: {{ $group->color_hex }}">
                                {{ number_format($group->points_cache) }} <span class="text-xs font-mono text-slate-500 font-normal">PTS</span>
                            </div>
                        </div>
                        <a href="{{ route('groups.show', $group->id) }}" class="px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider bg-slate-900 hover:bg-slate-800 text-white transition-all shadow-2xs">
                            View House →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
